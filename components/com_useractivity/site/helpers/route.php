<?php
/**
 * @package      User Activity
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


/**
 * User Activity Component Route Helper
 *
 */
abstract class UserActivityHelperRoute
{
    public function getActivitiesRoute()
    {
        static $itemid = null;

        // Try to find a suitable menu item
        if (is_null($itemid)) {
            $app    = JFactory::getApplication();
            $menu   = $app->getMenu();
            $com    = JComponentHelper::getComponent('com_useractivity');
            $items  = $menu->getItems('component_id', $com->id);

            if (!$items) {
                $itemid = 0;
            }
            else {
                foreach ($items as $item)
                {
                    if (isset($item->query['view']) && $item->query['view'] == 'activities') {
                        $itemid = $item->id;
                        break;
                    }
                }
            }
        }

        // Create link
        $link = 'index.php?option=com_useractivity&view=activities'
              . ($itemid ? '&Itemid=' . $itemid : '');

        return $link;
    }
}
