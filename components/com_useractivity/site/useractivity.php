<?php
/**
 * @package      User Activity
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_useractivity/models', 'UserActivityModel');

$controller = JControllerLegacy::getInstance('Useractivity');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
