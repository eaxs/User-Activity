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


class UserActivityController extends JControllerLegacy
{
    /**
     * The default view
     *
     * @var    string
     */
    protected $default_view = 'activities';


    public function display($cachable = false, $urlparams = false)
    {
        parent::display();

        return $this;
    }
}
