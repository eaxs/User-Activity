<?php
/**
 * @package      pkg_useractivity
 * @subpackage   mod_useractivity_site
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


$items       = array();
$com_params  = JComponentHelper::getParams('com_useractivity');
$date_rel    = $params->get('date_relative', $com_params->get('date_relative', 1));
$date_format = $params->get('date_format');
if (!$date_format) $date_format = JText::_('DATE_FORMAT_LC1');

foreach ($data['items'] AS $item)
{
    $date = JHtml::_('date', $item->created, $date_format);

    $html = '<li style="display:none;">'
          . '    <strong class="row-title">'
          .          $item->text
          . '    </strong>'
          . '    <p class="small">';

    if ($date_rel) :
        $html .= '<span class="hasTip" title="' . $date . '" style="cursor: help;">'
              .  UserActivityHelper::relativeDateTime($item->created)
              .  '</span>';
    else :
        $html .= $date;
    endif;

    $html .= '    </p>'
          . '</li>';

    $items[] = $html;
}

echo json_encode(array('total' => $data['total'], 'items' => $items));