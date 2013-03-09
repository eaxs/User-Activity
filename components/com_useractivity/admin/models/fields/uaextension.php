<?php
/**
 * @package      User Activity
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die();


JFormHelper::loadFieldClass('list');
JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_useractivity/models', 'UserActivityModel');

/**
 * HTML Form Field class for a user activity extension select list.
 *
 */
class JFormFieldUaextension extends JFormFieldList
{
    /**
     * The form field type.
     *
     * @var    string    
     */
    public $type = 'Uaextension';


    /**
     * Method to get the field options for extensions
     *
     * @return    array    The field option objects.
     */
    protected function getOptions()
    {
        $model   = JModelLegacy::getInstance('Activities', 'UserActivityModel', array('ignore_request' => true));
        $options = $model->getExtensions();

        // Merge any additional options in the XML definition.
        $options = array_merge(parent::getOptions(), $options);

        return $options;
    }
}
