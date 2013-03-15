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


if ($params->get('load_jquery', 1)) {
    JHtml::_('script', 'com_useractivity/jquery.min.js', false, true, false, false, false);
    JHtml::_('script', 'com_useractivity/jquery.noconflict.js', false, true, false, false, false);
}

JHtml::_('script', 'com_useractivity/jquery.module.js', false, true, false, false, false);

$id    = (int) $module->id;
$limit = (int) $params->get('list_limit');
$count = count($data['items']);
$start = 0;

$com_params  = JComponentHelper::getParams('com_useractivity');
$date_rel    = $params->get('date_relative', $com_params->get('date_relative', 1));
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
                <button type="button" class="actbtn-prev-<?php echo $id; ?> btn button disabled" onclick="uaPrev<?php echo $id; ?>(this);">
                    &lt;
                </button>
                <button type="button" class="actbtn-next-<?php echo $id; ?> btn button <?php if ($limit >=  $data['total']) echo ' disabled'; ?>" onclick="uaNext<?php echo $id; ?>(this);">
                    &gt;
                </button>
            </div>
            <!-- End Top Navigation -->

            <!-- Start Filters -->
            <?php
                if ($params->get('show_filter_extension')) :
                    $ext = $params->get('filter_extension');
                    if ($ext_empty) : ?>
                    <div class="fltrt">
                        <select name="filter_extension" onchange="uaFilter<?php echo $id;?>()" class="inputbox">
                            <option value=""><?php echo JText::_('MOD_USERACTIVITY_SITE_FIELD_OPTION_SELECT_EXTENSION'); ?></option>
                            <?php echo JHtml::_('select.options', $model->getExtensions(), 'value', 'text', $ext); ?>
                        </select>
                    </div>
                <?php endif;
            endif;
            ?>
            <?php if ($params->get('show_filter_event')) : ?>
                <div class="fltrt">
                    <select name="filter_event_id" onchange="uaFilter<?php echo $id;?>()" class="inputbox">
                        <option value=""><?php echo JText::_('MOD_USERACTIVITY_SITE_FIELD_OPTION_SELECT_EVENT'); ?></option>
                        <?php echo JHtml::_('select.options', $model->getEvents(), 'value', 'text', $params->get('filter_event_id')); ?>
                    </select>
                </div>
            <?php endif; ?>
            <!-- End Filters -->

            <!-- Start Search -->
            <?php if ($params->get('show_filter_search')) : ?>
                <div class="fltrt">
                    <input type="text" class="inputbox" placeholder="<?php echo JText::_('MOD_USERACTIVITY_SITE_FILTER_SEARCH_DESC'); ?>"
                        name="filter_search" value="" autocomplete="off" onkeyup="uaFilterSearch<?php echo $id;?>(this.value);"
                        title="<?php echo JText::_('MOD_USERACTIVITY_SITE_FILTER_SEARCH_DESC'); ?>"
                    />
                </div>
            <?php endif; ?>
            <!-- End Search -->
            <div class="clr" style="clear: both;"></div>
        </fieldset>
    <?php endif; ?>

    <!-- Start List -->
    <ul id="activities-<?php echo $id; ?>">
    	<?php if ($count) : ?>
                <?php
                foreach ($data['items'] as $i => $item) :
                    $date = JHtml::_('date', $item->created, $date_format);
                    ?>
                    <li>
                        <strong class="row-title"><?php echo $item->text; ?></strong>
                        <p class="small">
                            <?php
                            if ($date_rel) :
                                ?>
                                <span class="hasTip" title="<?php echo $date; ?>" style="cursor: help;">
                                    <?php echo UserActivityHelper::relativeDateTime($item->created); ?>
                                </span>
                                <?php
                            else :
                                echo $date;
                            endif;
                            ?>
                        </p>
                    </li>
                    <?php
                endforeach;
                ?>
    	<?php else : ?>
    		<li>
    			<?php echo JText::_('MOD_USERACTIVITY_SITE_NO_MATCHING_RESULTS');?>
    		</li>
    	<?php endif; ?>
    </ul>
    <div class="clr" style="clear: both;"></div>
    <!-- End List -->

    <!-- Start Bottom Navigation -->
    <?php if ($count) : ?>
        <fieldset>
            <div class="fltlft">
                <button type="button" class="actbtn-prev-<?php echo $id; ?> btn button disabled" onclick="uaPrev<?php echo $id; ?>(this);">
                    &lt;
                </button>
                <button type="button" class="actbtn-next-<?php echo $id; ?> btn button <?php if ($limit >=  $data['total']) echo ' disabled'; ?>" onclick="uaNext<?php echo $id; ?>(this);">
                    &gt;
                </button>
            </div>
            <div class="clr" style="clear: both;"></div>
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
