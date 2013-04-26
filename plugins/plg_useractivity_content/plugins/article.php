<?php
/**
 * @package      pkg_useractivity
 * @subpackage   plg_useractivity_content
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


/**
 * Content Article User Activity plugin
 *
 */
class plgUserActivityContentArticle extends plgUserActivity
{
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
        parent::onUserActivityAfterSave($context, $table, $is_new, false);

        // Set category as reference
        $this->item_data['xref_id'] = $table->catid;

        // Set meta data
        $this->item_data['metadata']->set('cat_alias', '');
        $this->item_data['metadata']->set('cat_title', '');

        if ($table->catid) {
            $cat = $this->getCategory($table->catid);

            if ($cat) {
                $this->item_data['metadata']->set('cat_alias', $cat->alias);
                $this->item_data['metadata']->set('cat_title', $cat->title);
            }
        }

        // Save activity
        if ($store) return $this->save();

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
        parent::onUserActivityAfterDelete($context, $table, false);

        // Set category as reference
        $this->item_data['xref_id'] = $table->catid;

        // Set meta data
        $this->item_data['metadata']->set('cat_alias', '');
        $this->item_data['metadata']->set('cat_title', '');

        if ($table->catid) {
            $cat = $this->getCategory($table->catid);

            if ($cat) {
                $this->item_data['metadata']->set('cat_alias', $cat->alias);
                $this->item_data['metadata']->set('cat_title', $cat->title);
            }
        }

        // Save activity
        if ($store) return $this->save();

        return true;
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
        parent::setDataFromItemState($id, $state);

        // Get the item
        $item = $this->getItem($id);
        if (!$item) return false;

        // Set the data
        $this->item_data['title']      = $item->title;
        $this->item_data['asset_id']   = $item->asset_id;
        $this->item_data['xref_id']    = $item->catid;
        $this->item_data['state']      = $item->state;
        $this->item_data['access']     = $item->access;

        // Set activity access
        $this->activity_data['access'] = $item->access;

        // Set meta data
        $this->item_data['metadata']->set('alias', $item->alias);
        $this->item_data['metadata']->set('cat_alias', $item->cat_alias);
        $this->item_data['metadata']->set('cat_title', $item->cat_title);
    }


    /**
     * Method to get a partial item record
     *
     * @param     integer    $id    The item id
     *
     * @return    object            The item record data
     */
    private function getItem($id)
    {
        static $cache = array();

        // Check the cache
        if (isset($cache[$id])) return $cache[$id];

        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('a.asset_id, a.catid, a.title, a.alias, a.state, a.access')
              ->select('c.title AS cat_title, c.alias AS cat_alias')
              ->from('#__content AS a')
              ->join('left', '#__categories AS c ON c.id = a.catid')
              ->where('a.id = ' . $db->quote((int) $id));

        $db->setQuery($query);
        $cache[$id] = $db->loadObject();

        return $cache[$id];
    }


    /**
     * Method to get a category title and alias
     *
     * @param     integer    $id    The category id
     *
     * @return    object            The title and alias
     */
    private function getCategory($id)
    {
        static $cache = array();

        // Check the cache
        if (isset($cache[$id])) return $cache[$id];

        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('title, alias')
              ->from('#__categories')
              ->where('id = ' . $db->quote((int) $id));

        $db->setQuery($query);
        $cache[$id] = (int) $db->loadObject();

        return $cache[$id];
    }
}
