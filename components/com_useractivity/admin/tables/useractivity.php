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


class JTableUserActivity extends JTable
{
    /**
     * Constructor
     *
     * @param    object    $db    A database connector object
     */
    public function __construct($db)
    {
        parent::__construct('#__user_activity', 'id', $db);
    }


    /**
     * Overloaded check function
     *
     * @return    boolean          True on success, false on failure
     *
     * @see       jtable::check
     */
    public function check()
    {
        if (!$this->created_by) {
            $this->created_by = (int) JFactory::getUser()->get('id');
        }

        if (!$this->created) {
            $this->created = JFactory::getDate()->toSql();
        }

        return true;
    }


    /**
     * Method to set the publishing state for a row or list of rows in the database
     * table. The method respects checked out rows by other users and will attempt
     * to checkin rows that it can after adjustments are made.
     *
     * @param     mixed      $pks        An optional array of primary key values to update.  If not set the instance property value is used.
     * @param     integer    $state      The publishing state. eg. [0 = unpublished, 1 = published]
     * @param     integer    $user_id    The user id of the user performing the operation.
     *
     * @return    boolean                True on success.
     */
    public function publish($pks = null, $state = 1, $user_id = 0)
    {
        $k = $this->_tbl_key;

        // Sanitise input.
        JArrayHelper::toInteger($pks);

        $state = (int) $state;

        // If there are no primary keys set check to see if the instance key is set.
        if (empty($pks)) {
            if ($this->$k) {
                $pks = array($this->$k);
            }
            else {
                // Nothing to set publishing state on, return false.
                $this->setError(JText::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));
                return false;
            }
        }

        // Build the WHERE clause for the primary keys.
		$where = $k . '=' . implode(' OR ' . $k . '=', $pks);

        // Get the JDatabaseQuery object
        $query = $this->_db->getQuery(true);

        // Update the publishing state for rows with the given primary keys.
        $query->update($this->_db->quoteName($this->_tbl))
              ->set($this->_db->quoteName('state') . ' = ' . (int) $state)
              ->where('(' . $where . ')');

        $this->_db->setQuery($query);

        try {
            $this->_db->execute();
        }
        catch (RuntimeException $e) {
            $this->setError($e->getMessage());
            return false;
        }

        // If the JTable instance value is in the list of primary keys that were set, set the instance.
        if (in_array($this->$k, $pks)) {
            $this->state = $state;
        }

        $this->setError('');

        return true;
    }
}
