<?php
/**
 * @package      pkg_useractivity
 * @subpackage   mod_useractivity_admin
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


if (version_compare(JVERSION, 3, 'ge')) {
    require_once dirname(__FILE__) . '/default_json_j30.php';
}
else {
    require_once dirname(__FILE__) . '/default_json_j25.php';
}
