<?php
/**
 * @package      pkg_useractivity
 * @subpackage   plg_useractivity_content
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


/**
 * Content User Activity plugin
 *
 */
class plgUserActivityContent extends JPlugin
{
    /**
     * List of supported contexts
     *
     * @var    array
     */
    protected $supported;

    /**
     * List of context aliases
     *
     * @var    array
     */
    protected $aliases;

    /**
     * Plugin config options
     *
     * @var    array
     */
    protected $config;



    /**
     * Constructor
     *
     * @param    object    $subject    The object to observe
     * @param    array     $config     An optional associative array of configuration settings.
     */
    public function __construct(&$subject, $config = array())
    {
        // Construct parent
        parent::__construct($subject, $config);

        // Set the config
        $this->config = $config;

        // Set supported contexts
        $this->supported = array(
            'com_content.article',
            'com_content.category'
        );

        // Set context aliases
        $this->aliases = array(
            'com_content.form' => 'com_content.article',
            'com_categories.category' => 'com_content.category'
        );
    }


    /**
     * Method to store user activity after a save event
     *
     * @param     string     $context    The item context
     * @param     object     $table      The item table object
     * @param     boolean    $is_new     New item indicator (True is new, False is update)
     *
     * @return    boolean                True on success, False on error
     */
    public function onUserActivityAfterSave($context, $table, $is_new)
    {
        // Unalias context
        $this->unalias($context);

        // Check if the context is supported
        if (!in_array($context, $this->supported)) return true;

        // Check if com_categories event is for com_content
        if ($context == 'com_content.category' && JRequest::getVar('extension') != 'com_content') {
            return true;
        }

        // Get the plugin class that handles this context
        if (!$plugin = $this->getPlugin($context)) return true;

        // Process activity
        return $plugin->onUserActivityAfterSave($context, $table, $is_new);
    }


    /**
     * Method to store user activity after a delete event
     *
     * @param     string     $context    The item context
     * @param     object     $table      The item table object
     *
     * @return    boolean                True on success, False on error
     */
    public function onUserActivityAfterDelete($context, $table)
    {
        // Unalias context
        $this->unalias($context);

        // Check if the context is supported
        if (!in_array($context, $this->supported)) return true;

        // Check if com_categories event is for com_content
        if ($context == 'com_content.category' && JRequest::getVar('extension') != 'com_content') {
            return true;
        }

        // Get the plugin class that handles this context
        if (!$plugin = $this->getPlugin($context)) return true;

        // Process activity
        return $plugin->onUserActivityAfterDelete($context, $table);
    }


    /**
     * Method to store user activity after a state change event
     *
     * @param     string     $context    The item context
     * @param     array      $pks        The item id's whose state was changed
     * @param     integer    $value      New state to which the items were changed
     *
     * @return    boolean                True on success, False on error
     */
    public function onUserActivityChangeState($context, $pks, $value)
    {
        // Unalias context
        $this->unalias($context);

        // Check if the context is supported
        if (!in_array($context, $this->supported)) return true;

        // Check if com_categories event is for com_content
        if ($context == 'com_content.category' && JRequest::getVar('extension') != 'com_content') {
            return true;
        }

        // Get the plugin class that handles this context
        if (!$plugin = $this->getPlugin($context)) return true;

        // Process activity
        return $plugin->onUserActivityChangeState($context, $pks, $value);
    }


    /**
     * Method to get a plugin class for handling the given context
     *
     * @param     string    $context    The item context
     *
     * @return    mixed                 Class instance on success, Null on error
     */
    protected function getPlugin($context)
    {
        static $cache = array();

        if (array_key_exists($context, $cache)) {
            return $cache[$context];
        }

        list($extension, $item) = explode('.', $context, 2);

        $file   = null;
        $class  = null;

        switch ($item)
        {
            case 'article':
                $file  = dirname(__FILE__) . '/plugins/article.php';
                $class = 'plgUserActivityContentArticle';
                break;

            case 'category':
                $file  = dirname(__FILE__) . '/plugins/category.php';
                $class = 'plgUserActivityContentCategory';
                break;
        }

        if (is_null($file) || !file_exists($file)) return $helper;

        require_once $file;

        if (!class_exists($class)) return false;

        $cache[$context] = new $class($this->_subject, $this->config);

        return $cache[$context];
    }


    /**
     * Method to un-alias a given context
     *
     * @param     string    $context    The item context
     *
     * @return    void
     */
    protected function unalias(&$context)
    {
        if (isset($this->aliases[$context])) {
            $context = $this->aliases[$context];
        }
    }
}
