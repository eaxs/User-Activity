<?php
/**
 * @package      User Activity
 * @subpackage   mod_useractivity_site
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


$items       = array();
$date_format = $params->get('date_format');
if (!$date_format) $date_format = JText::_('DATE_FORMAT_LC1');

foreach ($data['items'] AS $item)
{
    $html = '<div class="row-fluid" style="display:none;">'
          . '<div class="span12">'
          . '<strong class="row-title">' . $item->text . '</strong>'
          . '<p class="small"><i class="icon-calendar"></i> '
          . JHtml::_('date', $item->created, $date_format)
          . '</p>'
          . '</div>'
          . '</div>';

    $items[] = $html;
}

echo json_encode(array('total' => $data['total'], 'items' => $items));