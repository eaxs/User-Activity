<?php
/**
 * @package      pkg_useractivity
 * @subpackage   com_useractivity
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.modeladmin');


/**
 * Item Model for an activity record.
 *
 */
class UserActivityModelActivity extends JModelAdmin
{

    /**
     * Standard event Ids
     *
     * @var    array
     */
    protected $_std_events;

    /**
     * Cache for delta time
     *
     * @var    array
     */
    protected $_cache_delta;


    /**
     * Constructor.
     *
     * @param    array    $config    An optional associative array of configuration settings.
     */
    public function __construct($config = array())
    {
        parent::__construct($config);

        $this->_cache_delta = array();

        $this->_std_events = array(
            'save_new'    => 1,
            'save_update' => 2,
            'publish'     => 3,
            'unpublish'   => 4,
            'archive'     => 5,
            'trash'       => 6,
            'delete'      => 7
        );
    }


    /**
     * Abstract method for getting the form from the model.
     *
     * @param     array      $data        Data for the form.
     * @param     boolean    $loadData    True if the form is to load its own data (default case), false if not.
     *
     * @return    mixed                   A JForm object on success, false on failure
     */
    public function getForm($data = array(), $loadData = true)
    {
        // There is no form.
        return false;
    }


    /**
     * Returns a Table object, creating it if required.
     *
     * @param     string    $type      The table type to instantiate
     * @param     string    $prefix    A prefix for the table class name. Optional.
     * @param     array     $config    Configuration array for model. Optional.
     *
     * @return    object               A database object
     */
    public function &getTable($type = 'UserActivity', $prefix = 'JTable', $config = array())
    {
        static $table = array();

        $key = $type . $prefix . serialize($config);

        if (!isset($table[$key])) {
            $table[$key] = JTable::getInstance($type, $prefix, $config);
        }

        return $table[$key];
    }


    /**
     * Method to get the event id from the event name.
     *
     * @param     string     $name    The event name
     *
     * @return    integer             The event id. Returns 0 if no id was found.
     */
    public function getEventId($name)
    {
        // Check the standard events first
        if (isset($this->_std_events[$name])) {
            return $this->_std_events[$name];
        }

        // Not found, lets look it up from the table
        $query = $this->_db->getQuery(true);

        $query->select('id')
             ->from('#__user_activity_events')
             ->where('name = ' . $this->_db->quote($name));

        $this->_db->setQuery($query);
        $id = (int) $this->_db->loadResult();

        return $id;
    }


    /**
     * Method to get the event id from an item state
     *
     * @param     integer    $state    The item state
     *
     * @return    integer              The event id
     */
    public function getEventIdFromState($state)
    {
        switch ((int) $state)
        {
            case 0:
                return 4;
                break;

            case 1:
                return 3;
                break;

            case 2:
                return 5;
                break;

            case -2:
                return 6;
                break;
        }

        return 0;
    }


    /**
     * Method to save the activity data.
     * This is the same as the parent method, except with no plugin events triggering
     * to increase the speed a little.
     *
     * @param     array      $data    The form data.
     *
     * @return    boolean             True on success, False on error.
     */
    public function save($data)
    {
        $table  = $this->getTable();
        $key    = $table->getKeyName();
        $is_new = true;

        // Reset the table
        $table->reset();
        $table->$key = null;

        // Get the delta time if not set
        if (!isset($data['delta_time']) || empty($data['delta_time'])) {
            $data['delta_time'] = $this->getDeltaTime($data['type_id'], $data['event_id'], $data['created_by']);
        }

        /**
         * Table field "access" has been renamed to "vaccess"
         *
         * @since 1.2
         */
        if (!isset($data['vaccess'])) {
            if (isset($data['access'])) {
                $data['vaccess'] = $data['access'];

                unset($data['access']);
            }
            else {
                $data['vaccess'] = JFactory::getConfig()->get('access');
            }
        }

        // Allow an exception to be thrown.
        try
        {
            // Bind the data.
            if (!$table->bind($data)) {
                $this->setError($table->getError());
                return false;
            }

            // Prepare the row for saving
            $this->prepareTable($table);

            // Check the data.
            if (!$table->check()) {
                $this->setError($table->getError());
                return false;
            }

            // Store the data.
            if (!$table->store()) {
                $this->setError($table->getError());
                return false;
            }

            // Clean the cache.
            $this->cleanCache();
        }
        catch (Exception $e)
        {
            $this->setError($e->getMessage());
            return false;
        }

        $pkName = $table->getKeyName();

        if (isset($table->$pkName)) {
            $this->setState($this->getName() . '.id', $table->$pkName);
        }

        $this->setState($this->getName() . '.new', $is_new);

        return true;
    }


    /**
     * Method to save an event name into the user_activity_events table
     *
     * @param     string     $name    The event name
     *
     * @return    integer    $id      The newly created event id
     */
    public function saveEvent($name)
    {
        $obj = new stdClass();

        $obj->id   = null;
        $obj->name = $name;

        if (!$this->_db->insertObject('#__user_activity_events', $obj, 'id')) {
            return 0;
        }

        return (int) $obj->id;
    }


    /**
     * Method to get the minutes past since the last activity of the same extension, item, event and user
     *
     * @param     integer    $type      The item type id
     * @param     integer    $event     The event id
     * @param     integer    $author    The user id
     *
     * @return    integer               The minutes past
     */
    protected function getDeltaTime($type, $event, $author)
    {
        static $cache = array();

        // Check the cache
        $key = $type . '.' . $event . '.' . $author;
        if (isset($this->_cache_delta[$key])) return 0;

        $query = $this->_db->getQuery(true);

        $query->select('a.created')
              ->from('#__user_activity AS a')
              ->join('INNER', '#__user_activity_items AS i ON i.asset_id = a.item_id')
              ->where('i.type_id = ' . (int) $type)
              ->where('a.event_id = ' . (int) $event)
              ->where('a.created_by = ' . (int) $author)
              ->group('a.id')
              ->order('a.id DESC');

        $this->_db->setQuery($query, 0, 1);
        $date = $this->_db->loadResult();

        if (!$date) {
            $this->_cache_delta[$key] = 0;
            return $this->_cache_delta[$key];
        }

        $past = new JDate($date);
        $now  = new JDate();

        $this->_cache_delta[$key] = (floor($now->toUnix() / 60) - floor($past->toUnix() / 60));

        return $this->_cache_delta[$key];
    }


    /**
     * Method to auto-populate the model state.
     *
     * @return    void
     */
    protected function populateState()
    {
        // Do nothing
    }
}
