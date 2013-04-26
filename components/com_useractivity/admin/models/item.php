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
 * Item Model for an activity item.
 *
 */
class UserActivityModelItem extends JModelAdmin
{
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
    public function &getTable($type = 'Item', $prefix = 'UserActivityTable', $config = array())
    {
        static $table = array();

        $key = $type . $prefix . serialize($config);

        if (!isset($table[$key])) {
            $table[$key] = JTable::getInstance($type, $prefix, $config);
        }

        return $table[$key];
    }


    /**
     * Method to find an item type by extension and item name.
     *
     * @param     string     $extension    The extension name
     * @param     string     $name         The item name
     * @param     string     $plugin       Optional. If the plugin is provided, the type will be auto-created if not found
     *
     * @return    integer                  The extension type id
     */
    public function getType($extension, $name, $plugin = null)
    {
        static $cache = array();

        // Check the cache
        $key = $extension . '.' . $name;
        if (isset($cache[$key])) return $cache[$key];

        $query = $this->_db->getQuery(true);

        $query->select('id')
              ->from('#__user_activity_item_types')
              ->where('extension = ' . $this->_db->quote($extension))
              ->where('name = ' . $this->_db->quote($name));

        $this->_db->setQuery($query);
        $cache[$key] = (int) $this->_db->loadResult();

        if (!$cache[$key] && $plugin) {
            $obj = new stdClass();

            $obj->id        = null;
            $obj->plugin    = $plugin;
            $obj->extension = $extension;
            $obj->name      = $name;

            if (!$this->_db->insertObject('#__user_activity_item_types', $obj, 'id')) {
                $cache[$key] = 0;
                return $cache[$key];
            }

            $cache[$key] = $obj->id;
        }

        return $cache[$key];
    }


    /**
     * Method to save the activity item.
     *
     * @param     array      $data    The item data.
     *
     * @return    boolean             True on success, False on error.
     */
    public function save($data)
    {
        // Check if we have an asset id
        if (!isset($data['asset_id']) || empty($data['asset_id'])) {
            // We need at least an extension name, item name and id to find or create the asset.
            if (!isset($data['extension']) || !isset($data['name']) || !isset($data['id'])) {
                return false;
            }

            $asset_name  = $data['extension'] . '.' . $data['name'] . '.' . $data['id'];
            $asset_title = (isset($data['title']) ? $data['title'] : null);

            $data['asset_id'] = $this->getAssetId($asset_name, $asset_title, true);

            // Check if we have an asset id now
            if (!$data['asset_id']) return false;
        }

        /**
         * Check if we can update on the fly
         */
        $query = $this->_db->getQuery(true);

        // Load the item by asset id
        $query->select('type_id, xref_id, title, state, access, metadata')
              ->from('#__user_activity_items')
              ->where('asset_id = ' . (int) $data['asset_id']);

        $this->_db->setQuery($query, 0, 1);
        $row = $this->_db->loadAssoc();

        if (!empty($row)) {
            $obj           = new stdClass();
            $obj->asset_id = (int) $data['asset_id'];

            $update = false;

            // Check for changed title
            if (isset($data['title']) && $row['title'] != $data['title']) {
                $obj->title = $data['title'];
                $update = true;
            }

            // Check for changed reference id
            if (isset($data['xref_id']) && $row['xref_id'] != $data['xref_id']) {
                $obj->xref_id = $data['xref_id'];
                $update = true;
            }

            // Check for changed state
            if (isset($data['state']) && $row['state'] != $data['state']) {
                $obj->state = $data['state'];
                $update = true;
            }

            // Check for changed access
            if (isset($data['access']) && $row['access'] != $data['access']) {
                $obj->access = $data['access'];
                $update = true;
            }

            // Check for changed meta data
            $meta = (string) $data['metadata'];
            if ($meta != $row['metadata']) {
                $obj->metadata = $meta;
                $update = true;
            }

            if ($update) {
                // Update existing item
                if (!$this->_db->updateObject('#__user_activity_items', $obj, 'asset_id', false)) {
                    return false;
                }
            }

            $this->setState($this->getName() . '.id',   (int) $data['asset_id']);
            $this->setState($this->getName() . '.type', (int) $row['type_id']);
            $this->setState($this->getName() . '.new',  false);

            return true;
        }


        /**
         * Create new record
         */

        // Get item type if not set
        if (!isset($data['type_id']) || empty($data['type_id'])) {
            $data['type_id'] = $this->getType($data['extension'], $data['name'], $data['plugin']);

            if (!$data['type_id']) return false;
        }

        $obj = new stdClass();
        $obj->asset_id = (int) $data['asset_id'];
        $obj->type_id  = (int) $data['type_id'];
        $obj->xref_id  = (isset($data['xref_id']) ? (int) $data['xref_id']: 0);
        $obj->id       = (int) $data['id'];
        $obj->title    = $data['title'];
        $obj->state    = (isset($data['state']) ? (int) $data['state']: 1);
        $obj->access   = $data['access'];
        $obj->metadata = (string) $data['metadata'];

        if (!$this->_db->insertObject('#__user_activity_items', $obj, 'asset_id')) {
            return false;
        }

        $this->setState($this->getName() . '.id',   $obj->asset_id);
        $this->setState($this->getName() . '.type', $obj->type_id);
        $this->setState($this->getName() . '.new', true);

        return true;
    }


    /**
     * Method to get the extension id by its name.
     *
     * @param     string     $name    The extension name
     *
     * @return    integer             The extension id. Returns 0 if no id was found.
     */
    protected function getExtensionId($name)
    {
        static $cache = array();

        // Check the cache
        if (isset($cache[$name])) return $cache[$name];

        // Not yet in cache. Find id from extensions table
        $query = $this->_db->getQuery(true);

        $query->select('extension_id')
             ->from('#__extensions')
             ->where('name = ' . $this->_db->quote($name));

        $this->_db->setQuery($query);
        $cache[$name] = (int) $this->_db->loadResult();

        return $cache[$name];
    }


    /**
     * Method to get an asset ID, creating it if not found
     *
     * @param     string     $name      The asset name
     * @param     mixed      $title     The asset title
     * @param     boolean    $create    Create the asset if set to true and if not found
     *
     * @return    integer               The asset id
     */
    protected function getAssetId($name, $title = null, $create = false)
    {
        static $parents = array();

        $query = $this->_db->getQuery(true);

        $query->select('id')
              ->from('#__assets')
              ->where('name = ' . $this->_db->quote($name));

        $this->_db->setQuery($query);
        $asset_id = $this->_db->loadResult();

        if ($asset_id) return $asset_id;

        // Asset not found, create it if set
        if (!$create) return false;

        if (empty($title)) $title = $name;

        if (!isset($parents[$name])) {
            // Get the component asset id as parent for the new item
            list($ext, $item, $id) = explode('.', $name, 3);

            $query->clear()
                  ->select('id')
                  ->from('#__assets')
                  ->where('name = ' . $this->_db->quote('com_useractivity'));

            $this->_db->setQuery($query);
            $parents[$name] = (int) $this->_db->loadResult();
        }

        if (!$parents[$name]) return false;

        $asset = JTable::getInstance('Asset', 'JTable', array('dbo' => $this->_db));

        $asset->setLocation($parents[$name], 'last-child');

        $asset->parent_id = $parents[$name];
        $asset->name      = $name;
        $asset->title     = $title;

        if (!$asset->check() || !$asset->store(false)) {
            $this->setError($asset->getError());
            return false;
        }

        if ($asset->getError()) return false;

        return $asset->id;
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
