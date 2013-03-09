<?php
/**
 * @package      User Activity
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.view');


class UserActivityViewModule extends JViewLegacy
{
    /**
     * Displays the module.
     *
     */
    public function display($tpl = null)
    {
        // Check token
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $user = JFactory::getUser();

        // Get the module record
        $module = $this->get('Item');

        // Check for errors
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode("\n", $errors));
            jexit(500);
        }

        // Check if enabled
        if ($module->published != '1') {
            JError::raiseError(500, JText::_('COM_USERACTIVITY_ERROR_MODULE_ACCESS_DENIED'));
            jexit(500);
        }

        // Check access
        if (!$user->authorise('core.admin')) {
            $access = (int) $this->item->access;

            if (!in_array($access, $user->getAuthorisedViewLevels())) {
                JError::raiseError(500, JText::_('COM_USERACTIVITY_ERROR_MODULE_ACCESS_DENIED'));
                jexit(500);
            }
        }

        // Show only admin modules here
        if ($module->client_id != '1') {
            JError::raiseError(500, JText::_('COM_USERACTIVITY_ERROR_MODULE_ACCESS_DENIED'));
            jexit(500);
        }

        $name = $module->module;
        $file = JPATH_ADMINISTRATOR . '/modules/' . $name . '/' . $name . '.php';

        if (!file_exists($file)) {
            JError::raiseError(500, JText::_('COM_USERACTIVITY_ERROR_MODULE_FILE_NOT_FOUND'));
            jexit(500);
        }

        // Set the module params
        $this->setParamsFromRequest($module->params);
        $params = &$module->params;

        // Include the module
        require_once $file;
        jexit(201);
    }


    protected function setParamsFromRequest(&$params)
    {
        $app    = JFactory::getApplication();
        $layout = $params->get('layout', 'default') . '_json';
        $start  = $app->input->post->get('limitstart', 0);

        $filter_search = $app->input->post->get('filter_search', '');
        $filter_client = $app->input->post->get('filter_client_id', '');
        $filter_ext    = $app->input->post->get('filter_extension', '');
        $filter_event  = $app->input->post->get('filter_event_id', '');

        // Layout
        $params->set('layout', $layout);

        // List start
        $params->set('list_start', (int) $start);

        // Filter - Search
        if (!empty($filter_search)) {
            $params->set('filter_search', $filter_search);
        }

        // Filter - Location
        if (is_numeric($filter_client)) {
            $params->set('filter_client_id', (int) $filter_client);
        }

        // Filter Extension
        if (!empty($filter_ext)) {
            $params->set('filter_extension', $filter_ext);
        }

        // Filter - Event
        if (is_numeric($filter_event)) {
            $params->set('filter_event_id', $filter_event);
        }
    }
}
