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


jimport('joomla.application.component.modeladmin');


/**
 * Item Model for a module.
 *
 */
class UserActivityModelModule extends JModelLegacy
{
    /**
     * Method to load a module from the database
     *
     * @param     integer    $pk    The module ID to load (Optional)
     *
     * @return    mixed             Module record object or False on error
     */
    public function getItem($pk = null)
    {
        $pk    = (!empty($pk)) ? (int) $pk : (int) $this->getState( $this->getName() . '.id');
        $table = $this->getTable();

        if ($pk <= 0) {
            $this->setError(JText::_('COM_USERACTIVITY_ERROR_MODULE_NOT_FOUND'));
            return false;
        }

        // Attempt to load the row.
        $row = $table->load($pk);

        // Check for a table object error.
        if ($row === false && $error = $table->getError()) {
            $this->setError($error);
            return $false;
        }

        // Convert to the JObject.
        $properties = $table->getProperties(1);
        $item = JArrayHelper::toObject($properties, 'JObject');

        // Convert the params field to an array.
        $registry = new JRegistry;
        $registry->loadString($table->params);
        $item->params = $registry;

        return $item;
    }


    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param     string    $type      The table type to instantiate
     * @param     string    $prefix    A prefix for the table class name. Optional.
     * @param     array     $config    Configuration array for model. Optional.
     *
     * @return    jtable               A database object
    */
    public function getTable($type = 'Module', $prefix = 'JTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }


    /**
     * Method to auto-populate the model state.
     * Note: Calling getState in this method will result in recursion.
     *
     * @return    void
     */
    protected function populateState()
    {
        // Get the pk of the module from the request.
        $pk = JRequest::getInt('id');
        $this->setState($this->getName() . '.id', $pk);
    }
}
