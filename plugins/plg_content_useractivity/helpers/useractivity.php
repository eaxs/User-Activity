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
 * User Activity Translation Helper Class
 *
 */
class plgUserActivityHelper
{
    /**
     * Current user
     *
     * @var    object
     */
    protected $user;

    /**
     * Current user location
     *
     * @var    integer
     */
    protected $client_id;

    /**
     * Cache of translated titles
     *
     * @var    array
     */
    protected $cache_title = array();

    /**
     * Cache of translated user names
     *
     * @var    array
     */
    protected $cache_user = array();

    /**
     * Cache of translated language tokens
     *
     * @var    array
     */
    protected $cache_token = array();


    /**
     * Constructor
     *
     */
    public function __construct()
    {
        $this->user = JFactory::getUser();
        $this->client_id = (JFactory::getApplication()->isAdmin() ? 1 : 0);
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
        $key = strtoupper($item->extension) . '_UA_' . strtoupper($item->name) . '_' . strtoupper($item->event_name);

        // Check cache
        if (!isset($this->cache_token[$key])) {
            $this->cache_token[$key] = JText::_($key);
        }

        // Translate
        $item->text = sprintf(
            $this->cache_token[$key],
            $this->getUserName($item->created_by, $item->author_name),
            $this->getTitle($item->extension, $item->name, $item->item_id, $item->title, $item->asset_exists)
        );

        return $item;
    }


    /**
     * Method to translate a group of activity records
     *
     * @param     object    $group    The record group to translate
     *
     * @return    object    $item     The group, merged into a single item
     */
    public function translateGroup($group)
    {
        $key  = key($group);
        $item = $group[$key];
        $tkey = strtoupper($item->extension) . '_UA_' . strtoupper($item->name) . '_' . strtoupper($item->event_name) . '_N';

        // Prepare item title
        $titles = array();

        foreach ($group AS $el)
        {
            $titles[] = $this->getTitle($el->extension, $el->name, $el->item_id, $el->title, $el->asset_exists);
        }

        // Check cache
        if (!isset($this->cache_token[$tkey])) {
            $this->cache_token[$tkey] = JText::_($tkey);
        }

        // Translate
        $item->text = sprintf(
            $this->cache_token[$tkey],
            $this->getUserName($item->created_by, $item->author_name),
            count($group),
            implode(', ', $titles)
        );

        return $item;
    }


    /**
     * Method to translate a user name, adding a link to it if possible
     *
     * @param     integer    $id      The user id
     * @param     integer    $name    The user name
     *
     * @return    string              The translated user name
     */
    protected function getUserName($id, $name = null)
    {
        // Check the cache
        if (isset($this->cache_user[$id])) {
            return $this->cache_user[$id];
        }

        if ($this->getUserAccess($id)) {
            $this->cache_user[$id] = '<a href="' . $this->getUserLink($id) . '">' . htmlspecialchars($name, ENT_COMPAT, 'UTF-8') . '</a>';
        }
        else {
            $this->cache_user[$id] = htmlspecialchars($name, ENT_COMPAT, 'UTF-8');
        }

        return $this->cache_user[$id];
    }


    /**
     * Method to get the link to a user profile
     *
     * @param     integer    $id    The user id
     *
     * @return    string            The link to the profile
     */
    protected function getUserLink($id)
    {
        return JRoute::_('index.php?option=com_users&' . ($this->client_id ? 'task=user.edit' : 'view=profile') . '&id=' . (int) $id);
    }


    /**
     * Method to get check if the current user can access the acting user's profile
     *
     * @param     integer    $id    The user id
     *
     * @return    boolean           True if access granted, False if not
     */
    protected function getUserAccess($id)
    {
        return ($this->client_id ? $this->user->authorise('core.edit', 'com_users') : true);
    }


    /**
     * Method to translate an activity item title, adding a link to it if possible
     *
     * @param     string     $ext       The extension name
     * @param     string     $name      The extension item name
     * @param     integer    $id        The activity id
     * @param     string     $title     The activity title (Optional)
     * @param     integer    $exists    Whether the asset exists or not
     *
     * @return    string                The translated and formatted title
     */
    protected function getTitle($ext, $name, $id, $title = null, $exists = 0)
    {
        // Check the cache
        $key = $name . '.' . $id;

        if (isset($this->cache_title[$key])) {
            return $this->cache_title[$key];
        }

        $access = (($exists > 0) ? $this->getTitleAccess($ext, $name, $id) : false);

        if ($access) {
            $this->cache_title[$key] = '<a href="' . $this->getTitleLink($ext, $name, $id) . '">' . htmlspecialchars($title, ENT_COMPAT, 'UTF-8') . '</a>';
        }
        else {
            $this->cache_title[$key] = htmlspecialchars($title, ENT_COMPAT, 'UTF-8');
        }

        return $this->cache_title[$key];
    }


    protected function getTitleLink($ext, $name, $id)
    {
        return JRoute::_('index.php?option=' . $ext . '&' . ($this->client_id ? 'task=' . $name . '.edit' : 'view=' . $name) . '&id=' . (int) $id);
    }


    protected function getTitleAccess($ext, $name, $id)
    {
        return ($this->client_id ? $this->user->authorise('core.edit', $ext . '.' . $name . '.' . $id) : true);
    }
}
