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
     * User link destination
     *
     * @param    string
     */
    protected $user_link;

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
     * Current activity record that's being translated
     *
     * @var    object
     */
    protected $item = null;


    /**
     * Constructor
     *
     */
    public function __construct($config = array())
    {
        $this->user = JFactory::getUser();
        $this->client_id = (JFactory::getApplication()->isAdmin() ? 1 : 0);

        // Set the user link integration
        if (isset($config['user_link'])) {
            $this->user_link = $config['user_link'];
        }

        // Override link to joomla when in backend
        if ($this->client_id) {
            $this->user_link = 'joomla';
        }
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
        $this->item = $item;

        // Load meta data into JRegistry
        $metadata = $this->item->metadata;
        $this->item->metadata = new JRegistry();
        $this->item->metadata->loadString($metadata);

        $key = strtoupper($item->extension) . '_UA_' . strtoupper($item->name) . '_' . strtoupper($item->event_name);

        // Check cache
        if (!isset($this->cache_token[$key])) {
            $this->cache_token[$key] = JText::_($key);
        }

        // Translate
        $item->text = sprintf(
            $this->cache_token[$key],
            $this->getUserName(),
            $this->getTitle()
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
            $this->item = $el;

            // Load meta data into JRegistry
            $metadata = $this->item->metadata;
            $this->item->metadata = new JRegistry();
            $this->item->metadata->loadString($metadata);

            $titles[] = $this->getTitle();
        }

        $this->item = $item;

        // Load meta data into JRegistry
        $metadata = $this->item->metadata;
        $this->item->metadata = new JRegistry();
        $this->item->metadata->loadString($metadata);

        // Check cache
        if (!isset($this->cache_token[$tkey])) {
            $this->cache_token[$tkey] = JText::_($tkey);
        }

        // Translate
        $item->text = sprintf(
            $this->cache_token[$tkey],
            $this->getUserName(),
            count($group),
            implode(', ', $titles)
        );

        return $item;
    }


    /**
     * Method to translate a user name, adding a link to it if possible
     *
     * @return    string              The translated user name
     */
    protected function getUserName()
    {
        // Check the cache
        if (isset($this->cache_user[$this->item->created_by])) {
            return $this->cache_user[$this->item->created_by];
        }

        if ($this->getUserAccess() && ($this->user_link != 'nolink')) {
            $this->cache_user[$this->item->created_by] = '<a href="' . $this->getUserLink() . '">' . htmlspecialchars($this->item->author_name, ENT_COMPAT, 'UTF-8') . '</a>';
        }
        else {
            $this->cache_user[$this->item->created_by] = htmlspecialchars($this->item->author_name, ENT_COMPAT, 'UTF-8');
        }

        return $this->cache_user[$this->item->created_by];
    }


    /**
     * Method to get the link to a user profile
     *
     * @return    string            The link to the profile
     */
    protected function getUserLink()
    {
        if ($this->user_link == 'joomla' || empty($this->user_link)) {
            $link = 'index.php?option=com_users&'
                  . ($this->client_id ? 'task=user.edit' : 'view=profile')
                  . '&id=' . (int) $this->item->created_by
                  . ($this->client_id ? '' : ':' . $this->item->username);
        }
        else {
            $link = UserActivityHelperUserLink::get($this->item->created_by, $this->item->username, $this->user_link);
        }

        return JRoute::_($link);
    }


    /**
     * Method to get check if the current user can access the acting user's profile
     *
     * @return    boolean           True if access granted, False if not
     */
    protected function getUserAccess()
    {
        return ($this->client_id ? $this->user->authorise('core.edit', 'com_users') : true);
    }


    /**
     * Method to translate an activity item title, adding a link to it if possible
     *
     * @return    string                The translated and formatted title
     */
    protected function getTitle()
    {
        // Check the cache
        $key = $this->item->name . '.' . $this->item->item_id;

        if (isset($this->cache_title[$key])) {
            return $this->cache_title[$key];
        }

        $access = (($this->item->asset_exists > 0) ? $this->getTitleAccess() : false);

        if ($access) {
            $this->cache_title[$key] = '<a href="' . $this->getTitleLink() . '">' . htmlspecialchars($this->item->title, ENT_COMPAT, 'UTF-8') . '</a>';
        }
        else {
            $this->cache_title[$key] = htmlspecialchars($this->item->title, ENT_COMPAT, 'UTF-8');
        }

        return $this->cache_title[$key];
    }


    /**
     * Method to get the item title link
     *
     * @return    string              The title link
     */
    protected function getTitleLink()
    {
        $link = 'index.php?option=' . $this->item->extension . '&'
              . ($this->client_id ? 'task=' . $this->item->name . '.edit' : 'view=' . $this->item->name)
              . '&id=' . (int) $this->item->item_id;

        return JRoute::_($link);
    }


    /**
     * Method to check the access to an item
     *
     * @return    boolean             True on auth, False if not
     */
    protected function getTitleAccess()
    {
        $asset = $this->item->extension . '.' . $this->item->name . '.' . $this->item->item_id;

        if ($this->item->item_state != '1' && $this->client_id == 0) {
            if (!$this->user->authorise('core.edit.state', $asset)) {
                return false;
            }
        }

        return ($this->client_id ? $this->user->authorise('core.edit', $asset) : true);
    }
}
