<?php
/**
 * @package      User Activity
 * @subpackage   mod_useractivity_site
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


// Include the helper class.
jimport('joomla.application.module.helper');
require_once JPATH_SITE . '/modules/mod_useractivity_site/helper.php';

// Prepare model config
$config = array('ignore_request' => true);

if (is_numeric($params->get('group_activity'))) {
    $config['group_activity'] = (int) $params->get('group_activity');
}

// Get module data.
$model = modUserActivitySiteHelper::getModel($config);
$data  = modUserActivitySiteHelper::getItems($params);

// Render the module
require JModuleHelper::getLayoutPath('mod_useractivity_site', $params->get('layout', 'default'));
