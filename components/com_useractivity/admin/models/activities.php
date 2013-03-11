<?php
/**
 * @package      User Activity
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.modellist');
JLoader::register('UserActivityHelper', JPATH_ADMINISTRATOR . '/components/com_useractivity/helpers/useractivity.php');

// Load user activity plugin language files
UserActivityHelper::loadPluginLanguages();


/**
 * List Model for user activity.
 *
 */
class UserActivityModelActivities extends JModelList
{
    /**
     * Config option of whether to group activity or not
     *
     * @var    integer    
     */
    protected $group_activity;


    /**
     * Constructor
     *
     * @param    array    $config    An optional associative array of configuration settings.
     */
    public function __construct($config = array())
    {
        $params = JComponentHelper::getParams('com_useractivity', true);

        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'a.id', 'id',
                'a.created', 'created',
            );
        }

        if (!isset($config['group_activity'])) {
            $config['group_activity'] = (int) $params->get('group_activity', 5);
        }

        $this->group_activity = (int) $config['group_activity'];

        parent::__construct($config);
    }


    /**
     * Method to get a list of activities.
     *
     * @return    mixed    An array of data items on success, false on failure.
     */
    public function getItems()
    {
        // Get grouped?
        if ($this->group_activity > 0) {
            return $this->getItemsGrouped();
        }

        if (JDEBUG) JProfiler::getInstance('Application')->mark('beforeUserActivityGetItems');

        // Get the items from the database
        $store = $this->getStoreId();

        // Try to load the data from internal storage.
        if (isset($this->cache[$store])) return $this->cache[$store];

        // Load the list items.
        $query = $this->_getListQuery();
        $items = $this->_getList($query, $this->getStart(), $this->getState('list.limit'));

        // Check for a database error.
        if ($this->_db->getErrorNum()) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        if (!is_array($items)) $items = array();

        // Translate items
        $count   = count($items);
        $clients = array('0' => JText::_('JSITE'), '1' => JText::_('JADMINISTRATOR'));

        foreach ($items AS &$item)
        {
            // Translate client id
            $item->client = $clients[$item->client_id];

            // Translate the activity itself
            $item = UserActivityHelper::translate($item);
        }

        // Add the items to the internal cache.
        $this->cache[$store] = $items;

        if (JDEBUG) JProfiler::getInstance('Application')->mark('afterUserActivityGetItems');

        return $this->cache[$store];
    }


    /**
     * Method to get a list of grouped activity items
     *
     * @return    array    $items    The activity items
     */
    public function getItemsGrouped()
    {
        if (JDEBUG) JProfiler::getInstance('Application')->mark('beforeUserActivityGetItemsGrouped');

        $order_dir   = $this->state->get('list.direction', 'desc');
        $list        = parent::getItems();
        $items       = array();
        $new_frame   = array();
        $time_frames = array();

        if ($order_dir == 'desc') {
            // Must process items in chronological order
            $list = array_reverse($list);
        }

        // Group items into time frames
        foreach ($list AS &$item)
        {
            // Generate the frame key
            $frame_key = implode('.', array($item->event_id, $item->type_id, $item->created_by));

            // Create frame if not exists
            if (!isset($time_frames[$frame_key])) {
                $time_frames[$frame_key] = array();
                $new_frame[$frame_key]   = false;
            }

            // Check if the item is within the desired time frame
            if ((int) $item->delta_time > $this->group_activity) {
                $new_frame[$frame_key] = true;
            }

            // Create packet if not exists
            if (count($time_frames[$frame_key]) == 0 || $new_frame[$frame_key]) {
                $time_frames[$frame_key][] = array();

                end($time_frames[$frame_key]);
                $packet_key = key($time_frames[$frame_key]);

                $items[] = &$time_frames[$frame_key][$packet_key];

                $new_frame[$frame_key] = false;
            }
            else {
                end($time_frames[$frame_key]);

                $packet_key = key($time_frames[$frame_key]);
            }

            // Add segment/data to the packet
            if (isset($time_frames[$frame_key][$packet_key][$item->item_id]) && $order_dir == 'desc') {
                $time_frames[$frame_key][$packet_key][$item->item_id] = &$item;
            }
            else {
                $time_frames[$frame_key][$packet_key][$item->item_id] = &$item;
            }
        }

        // Translate the items
        $clients = array(JText::_('JSITE'), JText::_('JADMINISTRATOR'));

        foreach ($items AS &$item)
        {
            if (!is_array($item)) {
                $item->client = $clients[$item->client_id];
                $item = UserActivityHelper::translate($item);
                continue;
            }

            reset($item);

            if ($order_dir == 'desc') $item = array_reverse($item);

            if (count($item) == 1) {
                $key  = key($item);
                $item = UserActivityHelper::translate($item[$key]);
            }
            else {
                $item = UserActivityHelper::translate($item);
            }

            $item->client = $clients[$item->client_id];
        }

        if ($order_dir == 'desc') {
            // Reverse back to descending
            $items = array_reverse($items);
        }

        if (JDEBUG) JProfiler::getInstance('Application')->mark('afterUserActivityGetItemsGrouped');

        return $items;
    }


    /**
     * Build a list of extensions
     *
     * @return    array    The list of extensions
     */
    public function getExtensions()
    {
        $query = $this->_db->getQuery(true);
        $user  = JFactory::getUser();

        $query->select('a.name AS value')
              ->from('#__extensions AS a')
              ->join('INNER', '#__user_activity_item_types AS t ON t.extension = a.name')
              ->where('a.enabled = 1');

        // Implement View Level Access
        if (!$user->authorise('core.admin', 'com_useractivity')) {
            $levels   = $user->getAuthorisedViewLevels();
            $levels[] = 0;

            $query->where('a.access IN (' . implode(',', $levels) . ')');
        }

        $query->group('a.name');
        $query->order('a.name ASC');

        $this->_db->setQuery($query);
        $values = $this->_db->loadColumn();

        if (empty($values)) return array();

        $items = array();

        // Go through each item
        foreach ($values AS $i => $value)
        {
            $items[$i] = new stdClass();

            $items[$i]->value = $value;
            $items[$i]->text  = JText::_(strtoupper($value) . '_ACTIVITY');
        }

        return $items;
    }


    /**
     * Build a list of events
     *
     * @return    array    The list of events
     */
    public function getEvents()
    {
        $query = $this->_db->getQuery(true);

        // Setup the query
        $query->select('id AS value, name AS text')
              ->from('#__user_activity_events')
              ->order('name ASC');

        // Get the items
        $this->_db->setQuery($query);
        $items = (array) $this->_db->loadObjectList();

        // Go through each item
        foreach ($items AS &$item)
        {
            // Translate the component title
            $item->text = JText::_('COM_USERACTIVITY_EVENT_' . strtoupper($item->text));
        }

        return $items;
    }


    /**
     * Build a list of locations
     *
     * @return    array    The list of locations
     */
    public function getLocations()
    {
        $items = array(new stdClass, new stdClass);

        $items[0]->value = 0;
        $items[0]->text  = JText::_('JSITE');

        $items[1]->value = 1;
        $items[1]->text  = JText::_('JADMINISTRATOR');

        return $items;
    }


    /**
     * Method to get the total number of items for the data set.
     *
     * @return    integer    The total number of items available in the data set.
     */
    public function getTotal()
    {
        // Get a storage key.
        $store = $this->getStoreId('getTotal');

        // Try to load the data from internal storage.
        if (isset($this->cache[$store])) {
            return $this->cache[$store];
        }

        // Load the total.
        $this->setState('list.count', true);

        $query = $this->_getListQuery();
        $total = (int) $this->_getListCount($query);

        $this->setState('list.count', false);

        // Check for a database error.
        if ($this->_db->getErrorNum()) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        // Add the total to the internal cache.
        $this->cache[$store] = $total;

        return $this->cache[$store];
    }


    /**
     * Method to auto-populate the model state.
     * Note: Calling getState in this method will result in recursion.
     *
     * @param     string    $ordering     An optional ordering field.
     * @param     string    $direction    An optional direction (asc|desc).
     *
     * @return    void                    
     */
    protected function populateState($ordering = 'a.created', $direction = 'desc')
    {
        $app = JFactory::getApplication();

        // Adjust the context to support modal layouts.
        $layout = $app->input->get('layout');

        if ($layout) $this->context .= '.' . $layout;

        $this->setState('list.count', false);

        // Params
        if ($app->isSite()) {
            $this->setState('params', $app->getParams());
        }

        // Filter- Search
        $search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        // Filter - Author
        $author_id = $app->getUserStateFromRequest($this->context . '.filter.author_id', 'filter_author_id');
        $this->setState('filter.author_id', $author_id);

        // Filter - State
        $published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', 1, 'int');
        $this->setState('filter.published', $published);

        // Filter - Access
        $access = $this->getUserStateFromRequest($this->context.'.filter.access', 'filter_access', 0, 'int');
        $this->setState('filter.access', $access);

        // Filter - Event
        $event = $this->getUserStateFromRequest($this->context.'.filter.event_id', 'filter_event_id', '');
        $this->setState('filter.event_id', $event);

        // Filter - Location
        $client = $this->getUserStateFromRequest($this->context.'.filter.client_id', 'filter_client_id', 0, 'int');
        $this->setState('filter.client_id', $client);

        // Filter - Extension
        $extension = $app->getUserStateFromRequest($this->context . '.filter.extension', 'filter_extension', '');
        $this->setState('filter.extension', $extension);

        // Filter - Cross-reference
        $this->setState('filter.xref_id', '');

        parent::populateState($ordering, $direction);
    }


    /**
     * Method to get a store id based on model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param     string    $id    A prefix for the store id.
     *
     * @return    string           A store id.
     */
    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->getState('list.count');
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.access');
        $id .= ':' . $this->getState('filter.published');
        $id .= ':' . $this->getState('filter.author_id');
        $id .= ':' . serialize($this->getState('filter.extension_id'));
        $id .= ':' . $this->getState('filter.event_id');
        $id .= ':' . $this->getState('filter.client_id');
        $id .= ':' . $this->getState('filter.xref_id');

        return parent::getStoreId($id);
    }


    /**
     * Build an SQL query to load the list data.
     *
     * @return    object    Database query object
     */
    protected function getListQuery()
    {
        $count = $this->getState('list.count');
        $query = $this->_db->getQuery(true);
        $user  = JFactory::getUser();

        // Get possible filters
        $filter_search = $this->getState('filter.search');
        $filter_state  = $this->getState('filter.published');
        $filter_author = $this->getState('filter.author_id');
        $filter_ext    = $this->getState('filter.extension');
        $filter_event  = $this->getState('filter.event_id');
        $filter_client = $this->getState('filter.client_id');
        $filter_access = $this->getState('filter.access');
        $filter_xref   = $this->getState('filter.xref_id');

        // Select the required fields from the table.
        if ($count) {
            // Count items query
            $query->select('COUNT(a.id)')
                  ->from('#__user_activity AS a');

            // Join over the activity items for the item data
            if (!empty($filter_ext) || !empty($filter_search) || is_numeric($filter_xref)) {
                $query->join('INNER', '#__user_activity_items AS i ON i.asset_id = a.item_id');
                $query->join('INNER', '#__user_activity_item_types AS t ON t.id = i.type_id');
            }

            // Join over the users for the author.
            if (!empty($filter_search)) {
                $query->join('LEFT', '#__users AS ua ON ua.id = a.created_by');
            }
        }
        else {
            // Regular data query
            $query->select(
                $this->getState('list.select',
                    'a.id, a.client_id, a.event_id, a.created, a.created_by, a.delta_time, a.access, a.state'
                )
            );

            $query->select('i.asset_id, i.xref_id, i.id AS item_id, i.title');
            $query->select('t.id AS type_id, t.plugin, t.extension, t.name');
            $query->select('ae.id AS asset_exists');
            $query->select('ev.name AS event_name');
            $query->select('ua.name AS author_name');

            $query->from('#__user_activity AS a');

            // Join over the activity items for the item data
            $query->join('INNER', '#__user_activity_items AS i ON i.asset_id = a.item_id');

            // Join over the activity item types for the item type data
            $query->join('INNER', '#__user_activity_item_types AS t ON t.id = i.type_id');

            // Join over the assets to check if it still exists
            $query->join('LEFT', '#__assets AS ae ON ae.id = i.asset_id');

            // Join over the events for the event name.
            $query->join('LEFT', '#__user_activity_events AS ev ON ev.id = a.event_id');

            // Join over the users for the author.
            $query->join('LEFT', '#__users AS ua ON ua.id = a.created_by');
        }

        // Filter by location
        if (is_numeric($filter_client)) {
            $query->where('a.client_id = ' . (int) $filter_client);
        }
        else {
            $query->where('a.client_id = 0');
        }

        // Filter by published state
        if (is_numeric($filter_state)) {
            $query->where('a.state = ' . (int) $filter_state);
        }
        else {
            $query->where('a.state = 1');
        }

        // Filter by event
        if (is_numeric($filter_event)) {
            $query->where('a.event_id = ' . (int) $filter_event);
        }

        // Filter by author
        if (is_numeric($filter_author)) {
            $type = $this->getState('filter.author_id.include', true) ? ' = ' : ' <> ';

            $query->where('a.created_by' . $type . (int) $filter_author);
        }

        // Filter by extension
        if (!empty($filter_ext)) {
            if (!is_array($filter_ext)) {
                $query->where('t.extension = ' . $this->_db->quote($filter_ext));
            }
            else {
                if (count($filter_ext)) {
                    $names = array();

                    foreach ($filter_ext AS $ext)
                    {
                        if (empty($ext)) continue;
                        $names[] = $this->_db->quote($ext);
                    }

                    if (count($names)) {
                        $k     = 't.extension';
                        $where = $k . ' = ' . implode(' OR ' . $k . ' = ', $names);

                        $query->where('(' . $where . ')');
                    }

                }
            }
        }

        // Filter by cross reference
        if (is_numeric($filter_xref)) {
            $query->where('i.xref_id = ' . (int) $filter_xref);
        }

        // Filter by access
        if ($filter_access) {
            $query->where('a.access = ' . (int) $filter_access);
        }

        // Implement View Level Access
        if (!$user->authorise('core.admin', 'com_useractivity')) {
            $levels = implode(',', $user->getAuthorisedViewLevels());
            $query->where('a.access IN (' . $levels . ')');
        }

        // Filter by search
        if (!empty($filter_search)) {
            if (stripos($filter_search, 'id:') === 0) {
                $query->where('a.id = ' . (int) substr($filter_search, 3));
            }
            elseif (stripos($filter_search, 'author:') === 0) {
                $filter_search = $this->_db->quote($this->_db->escape(substr($filter_search, 7), true) . '%');
                $query->where('(ua.name LIKE ' . $filter_search . ' OR ua.username LIKE ' . $filter_search . ')');
            }
            else {
                $filter_search = $this->_db->quote('%' . $this->_db->escape($filter_search, true) . '%');
                $query->where('(i.title LIKE ' . $filter_search . ')');
            }
        }

        // Group by
        if (!$count) {
            $query->group('a.id');
        }

        // Order By clause
        $order_col = $this->state->get('list.ordering', 'a.created');
        $order_dir = $this->state->get('list.direction', 'desc');

        if ($order_col == 'a.created') {
            $order_col = 'a.id';
        }

        $query->order($this->_db->escape($order_col . ' ' . $order_dir));

        return $query;
    }


    /**
     * Returns a record count for the query
     *
     * @param     string     $query    The query.
     *
     * @return    integer              Number of rows for query
     */
    protected function _getListCount($query)
    {
        $this->_db->setQuery($query);
        $this->_db->execute();

        return (int) $this->_db->loadResult();
    }
}
