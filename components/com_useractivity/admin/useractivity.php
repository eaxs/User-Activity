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


// Access check
if (!JFactory::getUser()->authorise('core.manage', 'com_useractivity')) {
	return JError::raiseWarning(403, JText::_('JERROR_ALERTNOAUTHOR'));
}

// Include dependencies
jimport('joomla.application.component.controller');
jimport('joomla.application.component.helper');


// Register classes to autoload
JLoader::register('UserActivityHelper', JPATH_ADMINISTRATOR . '/components/com_useractivity/helpers/useractivity.php');

$controller = JControllerLegacy::getInstance('UserActivity');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
