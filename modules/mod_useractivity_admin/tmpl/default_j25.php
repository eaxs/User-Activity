<?php
/**
 * @package      User Activity
 * @subpackage   mod_useractivity_admin
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


if ($params->get('load_jquery', 1)) {
    JHtml::_('script', 'com_useractivity/jquery.min.js', false, true, false, false, false);
    JHtml::_('script', 'com_useractivity/jquery.noconflict.js', false, true, false, false, false);
}

JHtml::_('script', 'com_useractivity/jquery.module.js', false, true, false, false, false);

$id    = (int) $module->id;
$limit = (int) $params->get('list_limit');
$count = count($data['items']);
$start = 0;

$date_format = $params->get('date_format');
if (!$date_format) $date_format = JText::_('DATE_FORMAT_LC1');

$filter_ext = $params->get('filter_extension');
$ext_empty  = empty($filter_ext);

if (!$ext_empty && is_array($filter_ext)) {
    $empty = true;
    foreach ($filter_ext AS $ext)
    {
        if (empty($ext)) continue;
        $empty = false;
    }
    $ext_empty = $empty;
}
?>
<script type="text/javascript">
var fpv = '';

function uaNext<?php echo $id;?>(el)
{
    if (jQuery(el).hasClass('disabled') == false) {
        modUA.getItems('userActivityForm', '<?php echo $id; ?>', <?php echo $limit; ?>, 'next');
    }
}
function uaPrev<?php echo $id;?>(el)
{
    if (jQuery(el).hasClass('disabled') == false) {
        modUA.getItems('userActivityForm', '<?php echo $id; ?>', <?php echo $limit; ?>, 'prev');
    }
}
function uaFilter<?php echo $id;?>()
{
    modUA.getItems('userActivityForm', '<?php echo $id; ?>', <?php echo $limit; ?>, 'filter');
}
function uaFilterSearch<?php echo $id;?>(v)
{
    if (fpv == v) return;
    fpv = v;

    if (v.length > 2 || v.length == 0) {
        modUA.getItems('userActivityForm', '<?php echo $id; ?>', <?php echo $limit; ?>, 'filter');
    }
}
</script>
<form action="<?php echo JRoute::_('index.php?option=com_useractivity&view=module'); ?>" method="post"
    name="userActivityForm<?php echo $id; ?>"
    id="userActivityForm<?php echo $id; ?>"
    autocomplete="off"
    >

    <?php if ($count) : ?>
        <fieldset>
            <!-- Start Top Navigation -->
            <div class="fltlft">
                <button type="button" class="actbtn-prev-<?php echo $id; ?> btn disabled" onclick="uaPrev<?php echo $id; ?>(this);">
                    &lt;
                </button>
                <button type="button" class="actbtn-next-<?php echo $id; ?> btn <?php if ($limit >=  $data['total']) echo ' disabled'; ?>" onclick="uaNext<?php echo $id; ?>(this);">
                    &gt;
                </button>
            </div>
            <!-- End Top Navigation -->

            <!-- Start Filters -->
            <div class="fltrt">
                <select name="filter_client_id" onchange="uaFilter<?php echo $id;?>()" class="inputbox">
                    <?php echo JHtml::_('select.options', $model->getLocations(), 'value', 'text', (int) $params->get('filter_client_id')); ?>
                </select>
            </div>
            <?php
                $ext = $params->get('filter_extension');
                if ($ext_empty) : ?>
                <div class="fltrt">
                    <select name="filter_extension" onchange="uaFilter<?php echo $id;?>()" class="inputbox">
                        <option value=""><?php echo JText::_('MOD_USERACTIVITY_ADMIN_FIELD_OPTION_SELECT_EXTENSION'); ?></option>
                        <?php echo JHtml::_('select.options', $model->getExtensions(), 'value', 'text', $ext); ?>
                    </select>
                </div>
            <?php endif; ?>
            <div class="fltrt">
                <select name="filter_event_id" onchange="uaFilter<?php echo $id;?>()" class="inputbox">
                    <option value=""><?php echo JText::_('MOD_USERACTIVITY_ADMIN_FIELD_OPTION_SELECT_EVENT'); ?></option>
                    <?php echo JHtml::_('select.options', $model->getEvents(), 'value', 'text', $params->get('filter_event_id')); ?>
                </select>
            </div>
            <!-- End Filters -->

            <!-- Start Search -->
            <div class="fltrt">
                <input type="text" class="inputbox" placeholder="<?php echo JText::_('MOD_USERACTIVITY_ADMIN_FILTER_SEARCH_DESC'); ?>"
                    name="filter_search" value="" autocomplete="off" onkeyup="uaFilterSearch<?php echo $id;?>(this.value);"
                    title="<?php echo JText::_('MOD_USERACTIVITY_ADMIN_FILTER_SEARCH_DESC'); ?>"
                />
            </div>
            <!-- End Search -->
            <div class="clr"></div>
        </fieldset>
    <?php endif; ?>

    <!-- Start List -->
    <?php if ($count) : ?>
        <table class="adminlist">
            <thead>
                <tr>
                    <th>
                        <?php echo JText::_('MOD_USERACTIVITY_ADMIN_HEADING_ACTIVITY'); ?>
                    </th>
                    <th width="30%">
                        <?php echo JText::_('MOD_USERACTIVITY_ADMIN_HEADING_DATE'); ?>
                    </th>
                </tr>
            </thead>
            <tbody id="activities-<?php echo $id; ?>">
                <?php
                foreach ($data['items'] as $i => $item) :
                    ?>
                    <tr>
                        <td><strong class="row-title"><?php echo $item->text; ?></strong></td>
                        <td><?php echo JHtml::_('date', $item->created, $date_format); ?></td>
                    </tr>
                    <?php
                endforeach;
                ?>
            </tbody>
        </table>
    <?php else : ?>
        <p class="noresults"><?php echo JText::_('MOD_USERACTIVITY_ADMIN_NO_MATCHING_RESULTS');?></p>
    <?php endif; ?>
    <!-- End List -->

    <!-- Start Bottom Navigation -->
    <?php if ($count) : ?>
        <fieldset>
            <div class="fltlft">
                <button type="button" class="actbtn-prev-<?php echo $id; ?> btn disabled" onclick="uaPrev<?php echo $id; ?>(this);">
                    &lt;
                </button>
                <button type="button" class="actbtn-next-<?php echo $id; ?> btn <?php if ($limit >=  $data['total']) echo ' disabled'; ?>" onclick="uaNext<?php echo $id; ?>(this);">
                    &gt;
                </button>
            </div>
            <div class="clr"></div>
        </fieldset>
    <?php endif; ?>
    <!-- End Bottom Navigation -->

    <input type="hidden" name="id" value="<?php echo $id; ?>"/>
    <input type="hidden" value="<?php echo $start; ?>" name="limitstart"/>
    <input type="hidden" value="<?php echo $limit; ?>" name="limit"/>
    <input type="hidden" value="<?php echo $data['total']; ?>" name="total"/>
    <input type="hidden" value="0" name="busy"/>
    <?php echo JHtml::_('form.token'); ?>
</form>
