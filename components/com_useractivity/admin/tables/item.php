<?php
/**
 * @package      User Activity
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


/**
 * User Activity Item Table Class
 *
 */
class UserActivityTableItem extends JTable
{
    /**
     * Constructor
     *
     * @param    object    $db    A database connector object
     */
    public function __construct($db)
    {
        parent::__construct('#__user_activity_items', 'asset_id', $db);

        // Disable asset tracking
        $this->_trackAssets = false;
    }
}
