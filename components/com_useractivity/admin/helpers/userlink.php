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


/**
 * User Activity Component User Link Helper Class
 * Responsible for generating links to user profiles in the frontend
 *
 */
class UserActivityHelperUserLink
{
    /**
     * Method to link to a user profile page
     *
     * @param     integer    $id      The user id
     * @param     string     $name    The user name
     * @param     string     $dest    The component destination
     *
     * @return    string              The profile url
     */
    public static function get($id, $name = null, $dest = null)
    {
        switch ($dest)
        {
            case 'cb':
                return self::communityBuilder($id, $name);
                break;

            case 'jomsocial':
                return self::jomSocial($id, $name);
                break;

            case 'kunena':
                return self::kunena($id, $name);
                break;

            case 'projectfork':
                return self::projectfork($id, $name);
                break;

            default:
                return '#notfound';
                break;
        }
    }


    /**
     * Method to link to a Community Builder user profile page
     *
     * @param     integer    $id      The user id
     * @param     string     $name    The user name
     *
     * @return    string              The profile url
     */
    protected static function communityBuilder($id, $name = null)
    {
        static $itemid = null;

        // Try to find a suitable menu item
        if (is_null($itemid)) {
            $app	= JFactory::getApplication();
			$menu	= $app->getMenu();
			$com	= JComponentHelper::getComponent('com_comprofiler');
			$items	= $menu->getItems('component_id', $com->id);

            $profile_id = 0;
            $cb_id = 0;

			// If no items found, set to empty array.
			if (!$items) $items = array();

            foreach ($items as $item)
            {
                if (!isset($item->query['task']) || $item->query['task'] == 'userProfile') {
    				$itemid = $item->id;
    				break;
    			}
            }

            if (!$itemid) $itemid = 0;
        }

        // Create slug
        $slug = (int) $id . (empty($name) ? '' : ':' . $name);

        // Return link
        return 'index.php?option=com_comprofiler&task=userProfile&user=' . $slug . ($itemid ? '&Itemid=' . $itemid : '');
    }


    /**
     * Method to link to a JomSocial user profile page
     *
     * @param     integer    $id      The user id
     * @param     string     $name    The user name
     *
     * @return    string              The profile url
     */
    protected static function jomSocial($id, $name = null)
    {
        static $router = null;

        // Include the route helper once
        if (is_null($router)) {
            $file = JPATH_SITE . '/components/com_community/helpers/url.php';

            if (!file_exists($file)) {
                $router = false;
            }
            else {
                require_once $file;
                $router = true;
            }
        }

        // Return anchor link if the router was not found
        if (!$router) return '#notfound';

        // Return link
        return CUrlHelper::userLink((int) $id, false);
    }


    /**
     * Method to link to a Kunena user profile page
     *
     * @param     integer    $id      The user id
     * @param     string     $name    The user name
     *
     * @return    string              The profile url
     */
    protected static function kunena($id, $name = null)
    {
        static $router = null;

        // Include the route helper once
        if (is_null($router)) {
            $file = JPATH_SITE . '/components/com_kunena/lib/kunena.link.class.php';

            if (!file_exists($file)) {
                $router = false;
            }
            else {
                require_once $file;
                $router = true;
            }
        }

        // Return anchor link if the router was not found
        if (!$router) return '#notfound';

        // Return link
        return CKunenaLink::GetMyProfileURL((int) $id);
    }


    /**
     * Method to link to a Projectfork user profile page
     *
     * @param     integer    $id      The user id
     * @param     string     $name    The user name
     *
     * @return    string              The profile url
     */
    protected static function projectfork($id, $name = null)
    {
        static $router = null;

        // Include the route helper once
        if (is_null($router)) {
            $file = JPATH_SITE . '/components/com_pfusers/helpers/route.php';

            if (!file_exists($file)) {
                $router = false;
            }
            else {
                require_once $file;
                $router = true;
            }
        }

        // Return anchor link if the router was not found
        if (!$router) return '#notfound';

        // Create slug
        $slug = (int) $id . (empty($name) ? '' : ':' . $name);

        // Return link
        return PFusersHelperRoute::getUserRoute($slug);
    }
}
