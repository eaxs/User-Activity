<?php
/**
 * @package      User Activity
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

if (!$this->is_j25) {
    require_once dirname(__FILE__) . '/default_j30.php';
}
else {
    require_once dirname(__FILE__) . '/default_j25.php';
}