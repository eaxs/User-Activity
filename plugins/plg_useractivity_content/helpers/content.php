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


// Include translation helper class
require_once JPATH_PLUGINS . '/content/useractivity/helpers/useractivity.php';


/**
 * Content Activity Plugin Translation Helper Controller
 *
 */
class plgUserActivityContentHelper
{
    /**
     * Config options for the translation
     *
     * @var    array
     */
    protected $config;


    /**
     * Constructor
     *
     * @param    array   $config    Optional config options
     */
    public function __construct($config = array())
    {
        $this->config = $config;
    }


    /**
     * Method to translate an single activity record
     *
     * @param     object    $item    The record to translate
     *
     * @return    object    $item    The translated item
     */
    public function translateItem($item)
    {
        if (!$helper = $this->getHelper($item->name)) {
            return $item;
        }

        return $helper->translateItem($item);
    }


    /**
     * Method to translate a group of activity records
     *
     * @param     object    $group    The record group to translate
     *
     * @return    object              The group, merged into a single item
     */
    public function translateGroup($group)
    {
        $key = key($group);

        if (!$helper = $this->getHelper($group[$key]->name)) {
            return $group[$key];
        }

        return $helper->translateGroup($group);
    }


    /**
     * Method to get the translation helper for the corresponding extension
     *
     * @param     string    $name    The name of the item
     *
     * @return    mixed                   Helper class instance on success, False on error
     */
    protected function getHelper($name)
    {
        static $cache = array();

        // Check the cache
        if (isset($cache[$name])) {
            return $cache[$name];
        }

        $file  = null;
        $class = null;

        switch ($name)
        {
            case 'article':
                $file  = dirname(__FILE__) . '/article.php';
                $class = 'plgUserActivityContentArticleHelper';
                break;

            case 'category':
                $file  = dirname(__FILE__) . '/category.php';
                $class = 'plgUserActivityContentCategoryHelper';
                break;
        }

        if (is_null($file) || !file_exists($file)) return false;

        require_once $file;

        $cache[$name] = new $class($this->config);

        return $cache[$name];
    }
}
