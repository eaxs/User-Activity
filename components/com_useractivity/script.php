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


class com_useractivityInstallerScript
{
    /**
     * Called before any type of action
     *
     * @param     string              $route      Which action is happening (install|uninstall|discover_install)
     * @param     jadapterinstance    $adapter    The object responsible for running this script
     *
     * @return    boolean                         True on success
     */
    public function preflight($route, JAdapterInstance $adapter)
    {
        if (strtolower($route) == 'update') {
            $this->setSchemaVersion();
        }

        return true;
    }


    /**
     * Method to insert version id into the schemas table if not found
     *
     * @param     string     $current    The current version
     *
     * @return    boolean                True on success
     */
    protected function setSchemaVersion($current = '1.0.0')
    {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('extension_id')
              ->from('#__extensions')
              ->where('element = ' . $db->quote('com_useractivity'));

        $db->setQuery($query);
        $eid = (int) $db->loadResult();

        if (!$eid) return false;

        $query->clear()
              ->select('version_id')
              ->from('#__schemas')
              ->where('extension_id = ' . $eid);

        $db->setQuery($query);
        $version = $db->loadResult();

        if (empty($version)) {
            $query->clear()
                  ->insert('#__schemas')
                  ->columns(array('extension_id', 'version_id'))
                  ->values($eid . ', ' . $db->quote($current));

            $db->setQuery($query);
            $db->execute();
        }

        return true;
    }
}
