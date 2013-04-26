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

require_once JPATH_SITE . '/components/com_useractivity/helpers/route.php';
JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_useractivity/models', 'UserActivityModel');

$controller = JControllerLegacy::getInstance('Useractivity');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
