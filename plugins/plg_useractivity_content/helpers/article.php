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


if (JFactory::getApplication()->isSite()) {
    // Include the content route helper if we're in the frontend
    require_once JPATH_SITE . '/components/com_content/helpers/route.php';
}


/**
 * Content Article Activity Translation Helper Class
 *
 */
class plgUserActivityContentArticleHelper extends plgUserActivityHelper
{
    /**
     * Method to get the item title link
     *
     * @return    string              The title link
     */
    protected function getTitleLink()
    {
        if ($this->client_id) {
            $link = 'index.php?option=' . $this->item->extension
                  . '&task=' . $this->item->name . '.edit'
                  . '&id=' . (int) $this->item->item_id;
        }
        else {
            $item_slug = $this->item->item_id . ':' . $this->item->metadata->get('alias');
            $cat_alias = $this->item->metadata->get('cat_alias');
            $cat_slug  = ($this->item->xref_id && $cat_alias ? $this->item->xref_id . ':' . $cat_alias : 0);

            $link = ContentHelperRoute::getArticleRoute($item_slug, $cat_slug);
        }

        return JRoute::_($link);
    }
}