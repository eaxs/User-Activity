<?php
/**
 * @package      pkg_useractivity
 * @subpackage   plg_content_useractivity
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


/**
 * User Activity Plugin.
 *
 */
class plgUserActivity extends JPlugin
{
    /**
     * The activity data to save in an assoc array
     *
     * @var    array
     */
    protected $activity_data;

    /**
     * Instance of the component activity model
     *
     * @var    object
     */
    protected $activity_model;

    /**
     * The activity item data to save in an assoc array
     *
     * @var    array
     */
    protected $item_data;

    /**
     * Instance of the component activity item model
     *
     * @var    object
     */
    protected $item_model;

    /**
     * The current date time in sql format
     *
     * @var    string
     */
    protected $datetime;

    /**
     * The ID of the current user
     *
     * @var    integer
     */
    protected $user_id;

    /**
     * The location ID of the current user
     *
     * @var    integer
     */
    protected $client_id;


    /**
     * Constructor
     *
     * @param    object    $subject    The object to observe
     * @param    array     $config     An optional associative array of configuration settings.
     */
    public function __construct(&$subject, $config = array())
    {
        parent::__construct($subject, $config);

        // Prepare activity data
        $this->activity_data = array();
        $this->item_data     = array();

        $this->resetData();

        // Get the models
        $this->activity_model = JModelLegacy::getInstance('Activity', 'UserActivityModel');
        $this->item_model     = JModelLegacy::getInstance('Item', 'UserActivityModel');

        // Get the sql date time
        $this->datetime = JFactory::getDate()->toSql();

        // Get the user id
        $this->user_id = JFactory::getUser()->get('id');

        // Get the location id
        $this->client_id = (JFactory::getApplication()->isAdmin() ? 1 : 0);
    }


    /**
     * Method to store user activity after a save event
     *
     * @param     string     $context    The item context
     * @param     object     $table      The item table object
     * @param     boolean    $is_new     New item indicator (True is new, False is update)
     * @param     boolean    $store      Indicates whether to store the data or not
     *
     * @return    boolean                True on success, False on error
     */
    public function onUserActivityAfterSave($context, $table, $is_new, $store = true)
    {
        // Break the context into its parts
        list($extension, $item_name) = explode('.', $context, 2);

        // Set data from context
        $this->setDataFromContext($extension, $item_name, ($is_new ? 1 : 2));

        // Set data from table
        $this->setDataFromTable($table);

        // Set author data
        $this->setAuthorData();

        // Store data?
        if ($store) $this->save();

        return true;
    }


    /**
     * Method to store user activity after a delete event
     *
     * @param     string     $context    The item context
     * @param     object     $table      The item table object
     * @param     boolean    $store      Indicates whether to store the data or not
     *
     * @return    boolean                True on success, False on error
     */
    public function onUserActivityAfterDelete($context, $table, $store = true)
    {
        // Break the context into its parts
        list($extension, $item_name) = explode('.', $context, 2);

        // Set data from context
        $this->setDataFromContext($extension, $item_name, 7);

        // Set data from table
        $this->setDataFromTable($table);

        // Set author data
        $this->setAuthorData();

        // Store data?
        if ($store) $this->save();

        return true;
    }


    /**
     * Method to store user activity after a state change event
     *
     * @param     string     $context    The item context
     * @param     array      $pks        The item id's whose state was changed
     * @param     integer    $value      New state to which the items were changed
     * @param     boolean    $store      Indicates whether to store the data or not
     *
     * @return    boolean                True on success, False on error
     */
    public function onUserActivityChangeState($context, $pks, $value, $store = true)
    {
        // Break the context into its parts
        list($extension, $item_name) = explode('.', $context, 2);

        // Set the event id
        $event_id = $this->activity_model->getEventIdFromState($value);

        foreach ($pks AS $id)
        {
            $this->resetData();

            // Set data from context
            $this->setDataFromContext($extension, $item_name, $event_id);

            // Set author data
            $this->setAuthorData();

            // Set data from item id
            $this->setDataFromItemState($id, $value);

            if ($store) $this->save();
        }

        return true;
    }


    /**
     * Method to set some of the activity data from the given context
     *
     * @param     string     $extension    The name of the extension
     * @param     string     $item         The name of the item
     * @param     integer    $event        The event id
     *
     * @return    void
     */
    protected function setDataFromContext($extension, $item, $event)
    {
        // Set the extension name
        $this->item_data['extension']     = $extension;
        $this->activity_data['extension'] = $extension;

        // Set the item name
        $this->item_data['name']     = $item;
        $this->activity_data['name'] = $item;

        // Set the activity event id
        $this->activity_data['event_id'] = $event;
    }


    /**
     * Method to set some of the activity data from the given table instance
     *
     * @param     object    $table    The name of the extension
     *
     * @return    void
     */
    protected function setDataFromTable($table)
    {
        // Set the id of the item
        $pk = $table->getKeyName();
        $this->item_data['id'] = $table->$pk;

        // Set the title(guesswork required here)
        if (isset($table->title)) {
            $this->item_data['title'] = $table->title;
        }
        elseif (isset($table->name)) {
            $this->item_data['title'] = $table->name;
        }
        else {
            $this->item_data['title'] = null;
        }

        // Set the item asset id
        $this->item_data['asset_id'] = (isset($table->asset_id) ? (int) $table->asset_id : 0);

        // Set the item state
        $this->item_data['state'] = (isset($table->state) ? (int) $table->state : (isset($table->published) ? (int) $table->published : 1));

        // Set the item access
        $this->item_data['access'] = (isset($table->access) ? (int) $table->access : (int) JFactory::getConfig()->get('access'));

        // Set the activity access
        $this->activity_data['access'] = $this->item_data['access'];

        // Set item meta data
        if (isset($table->alias)) {
            $this->item_data['metadata']->set('alias', $table->alias);
        }
    }


    /**
     * Method to set some of the activity data from the item id and state
     *
     * @param     integer    $id       The item id
     * @param     integer    $state    The item state
     *
     * @return    void
     */
    protected function setDataFromItemState($id, $state)
    {
        // Set the id of the item
        $this->item_data['id']    = (int) $id;
        $this->item_data['state'] = (int) $state;
    }


    /**
     * Method to set the activity author information
     *
     * @return    void
     */
    protected function setAuthorData()
    {
        // Set author id
        $this->activity_data['created_by'] = $this->user_id;

        // Set creation date
        $this->activity_data['created'] = $this->datetime;

        // Set location
        $this->activity_data['client_id'] = $this->client_id;
    }


    /**
     * Method to save the activity to the database
     *
     * @return    boolean    True on success, False on error
     */
    protected function save()
    {
        // Set state if not set
        if (!isset($this->activity_data['state'])) {
            $this->activity_data['state'] = 1;
        }

        // Set access if not set
        if (!isset($this->activity_data['access'])) {
            $this->activity_data['access'] = (int) JFactory::getConfig()->get('access');
        }

        // Save the item
        if (!$this->item_model->save($this->item_data)) {
            return false;
        }

        // Fill in activity id if not set
        if (!isset($this->activity_data['item_id']) || empty($this->activity_data['item_id'])) {
            $item_id = $this->item_model->getState($this->item_model->getName() . '.id');
            $this->activity_data['item_id'] = $item_id;
        }

        // Fill in activity type id if not set
        if (!isset($this->activity_data['type_id']) || empty($this->activity_data['type_id'])) {
            $type_id = $this->item_model->getState($this->item_model->getName() . '.type');
            $this->activity_data['type_id'] = $type_id;
        }

        // Fill in activity days since epoch if not set
        if (!isset($this->activity_data['created_day']) || empty($this->activity_data['created_day'])) {
            $this->activity_data['created_day'] = floor(time() / 86400);
        }

        // Save the activity
        if (!$this->activity_model->save($this->activity_data)) {
            return false;
        }

        return true;
    }


    /**
     * Method to reset the activity data.
     * Note that it will keep the current plugin id though.
     *
     * @return    void
     */
    protected function resetData()
    {
        $this->activity_data = array('id' => null);
        $this->item_data     = array(
            'asset_id' => null,
            'plugin'   => $this->_type . '.' . $this->_name,
            'metadata' => new JRegistry());

        if ($this->activity_model) {
            $this->activity_model->setState($this->activity_model->getName() . '.id', null);
        }

        if ($this->item_model) {
            $this->item_model->setState($this->item_model->getName() . '.id',   null);
            $this->item_model->setState($this->item_model->getName() . '.type', null);
        }
    }
}
