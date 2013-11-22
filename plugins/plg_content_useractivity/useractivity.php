<?php
/**
 * @package      pkg_useractivity
 * @subpackage   plg_content_useractivity
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


// Register the component model and table
JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_useractivity/models', 'UserActivityModel');

// Register component table classes
JLoader::register('UserActivityTableItem', JPATH_ADMINISTRATOR . '/components/com_useractivity/tables/item.php');
JLoader::register('JTableUserActivity',    JPATH_ADMINISTRATOR . '/components/com_useractivity/tables/useractivity.php');
// JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_useractivity/tables');

// Register the activity plugin class
JLoader::register('plgUserActivity', dirname(__FILE__) . '/plugin.php');

// Include the user activity plugins
JPluginHelper::importPlugin('useractivity');


/**
 * User Activity Content Plugin.
 *
 */
class plgContentUserActivity extends JPlugin
{
    /**
     * Event dispatcher instance
     *
     * @var    object
     */
    protected $dispatcher;

    /**
     * List of unsupported contexts
     *
     * @var    array
     */
    protected $unsupported;


    /**
     * Constructor
     *
     * @param    object    $subject    The object to observe
     * @param    array     $config     An optional associative array of configuration settings.
     */
    public function __construct(&$subject, $config = array())
    {
        // Call parent contructor first
        parent::__construct($subject, $config);

        // Get the dispatcher
        $this->dispatcher = JDispatcher::getInstance();

        // Set unsupported contexts
        $this->unsupported = array(
            'com_useractivity.item',
            'com_useractivity.event',
            'com_useractivity.activity'
        );
    }


    /**
     * Triggers User Activity Plugins for the "onContentAfterSave" event
     *
     * @param     string     $context    The item context
     * @param     object     $table      The item table object
     * @param     boolean    $is_new     New item indicator (True is new, False is update)
     *
     * @return    boolean                True
     */
    public function onContentAfterSave($context, $table, $is_new)
    {
        if (!in_array($context, $this->unsupported)) {
            if (JDEBUG) {
                JProfiler::getInstance()->mark('beforeUserActivity' . $context);
            }

            if (JFactory::getUser()->authorise('core.create', 'com_useractivity')) {
                $this->dispatcher->trigger('onUserActivityAfterSave', array($context, $table, $is_new));
            }

            if (JDEBUG) {
                JFactory::getApplication()->enqueueMessage(JProfiler::getInstance()->mark('afterUserActivity' . $context), 'notice');
            }
        }

        return true;
    }


    /**
     * Triggers User Activity Plugins for the "onContentAfterDelete" event
     *
     * @param     string     $context    The item context
     * @param     object     $table      The item table object
     *
     * @return    boolean                True
     */
    public function onContentAfterDelete($context, $table)
    {
        if (!in_array($context, $this->unsupported)) {
            if (JDEBUG) {
                JProfiler::getInstance()->mark('beforeUserActivity' . $context);
            }

            if (JFactory::getUser()->authorise('core.create', 'com_useractivity')) {
                $this->dispatcher->trigger('onUserActivityAfterDelete', array($context, $table));
            }

            if (JDEBUG) {
                JFactory::getApplication()->enqueueMessage(JProfiler::getInstance()->mark('afterUserActivity' . $context), 'notice');
            }
        }

        return true;
    }


    /**
     * Triggers User Activity Plugins for the "onContentChangeState" event
     *
     * @param     string     $context    The item context
     * @param     array      $pks        The item id's whose state was changed
     * @param     integer    $value      New state to which the items were changed
     *
     * @return    boolean                True
     */
    public function onContentChangeState($context, $pks, $value)
    {
        if (!in_array($context, $this->unsupported)) {
            if (JDEBUG) {
                JProfiler::getInstance()->mark('beforeUserActivity' . $context);
            }

            if (JFactory::getUser()->authorise('core.create', 'com_useractivity')) {
                $this->dispatcher->trigger('onUserActivityChangeState', array($context, $pks, $value));
            }

            if (JDEBUG) {
                JFactory::getApplication()->enqueueMessage(JProfiler::getInstance()->mark('afterUserActivity' . $context), 'notice');
            }
        }

        return true;
    }
}
