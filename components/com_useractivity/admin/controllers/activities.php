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
 * User Activities list controller class.
 *
 */
class UserActivityControllerActivities extends JControllerAdmin
{
    /**
     * Proxy for getModel.
     *
     * @param     string    $name      The name of the model.
     * @param     string    $prefix    The prefix for the PHP class name.
     *
     * @return    object
     */
    public function getModel($name = 'Activity', $prefix = 'UserActivityModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }
}
