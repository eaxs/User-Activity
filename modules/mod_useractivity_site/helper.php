<?php
/**
 * @package      pkg_useractivity
 * @subpackage   mod_useractivity_site
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_useractivity/models', 'UserActivityModel');

/**
 * Helper for mod_useractivity_admin
 *
 */
abstract class modUserActivitySiteHelper
{
    public static $model = array();


    public static function getModel($config = array('ignore_request' => true))
    {
        $cache_key = serialize($config);

        if (!array_key_exists($cache_key, self::$model)) {
            self::$model[$cache_key] = JModelLegacy::getInstance('Activities', 'UserActivityModel', $config);
        }

        return self::$model[$cache_key];
    }


    /**
     * Get a list of the activities
     *
     * @param     jobject    The module parameters.
     *
     * @return    array
     */
    public static function getItems($params)
    {
        $user    = JFactory::getUser();
        $user_id = $user->get('id');
        $config  = array('ignore_request' => true);
        $data    = array();

        if (is_numeric($params->get('group_activity'))) {
            $config['group_activity'] = (int) $params->get('group_activity');
        }

        if ($params->get('user_link') != '') {
            $config['user_link'] = $params->get('user_link');
        }

        // Get the model
        $model = self::getModel($config);

        // Get possible filters
        $filter_author = $params->get('filter_author_id');
        $filter_ext    = $params->get('filter_extension');
        $filter_client = $params->get('filter_client_id');
        $filter_event  = $params->get('filter_event_id');
        $filter_search = $params->get('filter_search');

        // Set User Filter.
        switch ($filter_author)
        {
            case 'by_me':
                $model->setState('filter.author_id', $user_id);
                break;

            case 'not_me':
                $model->setState('filter.author_id', $user_id);
                $model->setState('filter.author_id.include', false);
                break;
        }

        // Set the extension filter
        if (!empty($filter_ext)) {
            $model->setState('filter.extension', $filter_ext);
        }

        // Set the location filter
        if (is_numeric($filter_client)) {
            $model->setState('filter.client_id', (int) $filter_client);
        }

        // Set the event filter
        if (is_numeric($filter_event)) {
            $model->setState('filter.event_id', (int) $filter_event);
        }

        // Set the search filter
        if (!empty($filter_search)) {
            $model->setState('filter.search', $filter_search);
        }

        // Set Ordering filter
        $model->setState('list.ordering', 'a.created');
        $model->setState('list.direction', 'desc');

        // Set the Start and Limit
        $model->setState('list.start', (int) $params->get('list_start', 0));
        $model->setState('list.limit', (int) $params->get('list_limit', 20));

        // Get the items
        $data['items'] = $model->getItems();
        $data['total'] = $model->getTotal();

        return $data;
    }
}
