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
 * Content Category User Activity plugin
 *
 */
class plgUserActivityContentCategory extends plgUserActivity
{
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
        $this->item_data['title']    = $item->title;
        $this->item_data['asset_id'] = $item->asset_id;
        $this->item_data['state']    = $item->state;
        $this->item_data['access']   = $item->access;

        // Set activity access
        $this->activity_data['access'] = $item->access;

        // Set meta data
        $this->item_data['metadata']->set('alias', $item->alias);
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

        $query->select('asset_id, title, alias, published AS state, access')
              ->from('#__categories')
              ->where('id = ' . $db->quote((int) $id));

        $db->setQuery($query);
        $cache[$id] = $db->loadObject();

        return $cache[$id];
    }
}
