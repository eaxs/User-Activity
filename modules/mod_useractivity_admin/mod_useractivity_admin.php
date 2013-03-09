<?php
/**
 * @package      User Activity
 * @subpackage   mod_useractivity_admin
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


// Include the helper class.
jimport('joomla.application.module.helper');
require_once JPATH_ADMINISTRATOR  . '/modules/mod_useractivity_admin/helper.php';

// Prepare model config
$config = array('ignore_request' => true);

if (is_numeric($params->get('group_activity'))) {
    $config['group_activity'] = (int) $params->get('group_activity');
}

// Get module data.
$model = modUserActivityAdminHelper::getModel($config);
$data  = modUserActivityAdminHelper::getItems($params);

// Render the module
require JModuleHelper::getLayoutPath('mod_useractivity_admin', $params->get('layout', 'default'));
