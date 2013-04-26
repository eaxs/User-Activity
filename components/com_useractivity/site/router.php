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
 * Build the route for the com_useractivity component
 *
 * @param     array    An array of URL arguments
 *
 * @return    array    The URL arguments to use to assemble the subsequent URL.
 */
function UserActivityBuildRoute(&$query)
{
    $segments = array();
    $app      = JFactory::getApplication();
    $menu     = $app->getMenu();

    // we need a menu item.  Either the one specified in the query, or the current active one if none specified
    if (empty($query['Itemid'])) {
        $menu_item = $menu->getActive();
        $menu_item_given = false;
    }
    else {
        $menu_item = $menu->getItem($query['Itemid']);
        $menu_item_given = true;
    }

    if (isset($query['view'])) {
        $view = $query['view'];
    }
    else {
        // we need to have a view in the query or it is an invalid URL
        return $segments;
    }


    if (($menu_item instanceof stdClass) && $menu_item->query['view'] == $query['view']) {
        unset($query['view']);

        if (isset($query['layout'])) unset($query['layout']);

        return $segments;
    }


    if ($view == 'activities') {
        if (!$menu_item_given) $segments[] = $view;

        unset($query['view']);
    }

    // if the layout is specified and it is the same as the layout in the menu item, we
    // unset it so it doesn't go into the query string.
    if (isset($query['layout'])) {
        if ($menu_item_given && isset($menu_item->query['layout'])) {
            if ($query['layout'] == $menu_item->query['layout']) {
                unset($query['layout']);
            }
        }
        else {
            if ($query['layout'] == 'default') unset($query['layout']);
        }
    }

    return $segments;
}


/**
 * Parse the segments of a URL.
 *
 * @param     array    The segments of the URL to parse.
 *
 * @return    array    The URL attributes to be used by the application.
 */
function UserActivityParseRoute($segments)
{
    $vars = array();

    if (count($segments)) {
        $vars['view'] = $segments[0];
    }

    return $vars;
}
