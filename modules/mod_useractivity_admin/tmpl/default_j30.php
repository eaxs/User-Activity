<?php
/**
 * @package      pkg_useractivity
 * @subpackage   mod_useractivity_admin
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


JHtml::_('bootstrap.tooltip');
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

$style = '.label-project {'
        . 'background-color: #80699B;'
        . '}'
        . '.label-milestone {'
        . 'background-color: #4572A7;'
        . '}'
        . '.label-tasklist {'
        . 'background-color: #8bbc21;'
        . '}'
        . '.label-task {'
        . 'background-color: #910000;'
        . '}'
        . '.label-time {'
        . 'background-color: #1aadce;'
        . '}'
        . '.label-topic {'
        . 'background-color: #492970;'
        . '}'
        . '.label-reply {'
        . 'background-color: #fc7136;'
        . '}'
        . '.label-design {'
        . 'background-color: #f28f43;'
        . '}'
        . '.label-category {'
        . 'background-color: #DB843D;'
        . '}'
        . '.label-article {'
        . 'background-color: #95b262;'
        . '}'
        . '.row-striped .img-circle {'
        . 'margin: 0 10px 0 0;'
        . '}';

JFactory::getDocument()->addStyleDeclaration( $style );
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
    	<div class="btn-toolbar">

            <!-- Start Search -->
            <div class="btn-group pull-right">
                <a class="btn" data-toggle="collapse" data-target="#act-filters-<?php echo $id; ?>" href="#">
                    <i class="icon-filter"></i>
                </a>
            </div>
            <div class="btn-group pull-left">
                <input type="text" class="search-query" placeholder="<?php echo JText::_('MOD_USERACTIVITY_ADMIN_FILTER_SEARCH_DESC'); ?>"
                    name="filter_search" value="" autocomplete="off" onkeyup="uaFilterSearch<?php echo $id;?>(this.value);"
                    title="<?php echo JText::_('MOD_USERACTIVITY_ADMIN_FILTER_SEARCH_DESC'); ?>"
                />
            </div>
            <!-- End Search -->

            <div class="clearfix"></div>
    	</div>

        <div class="clearfix"></div>

        <!-- Start Filters -->
        <div class="collapse" id="act-filters-<?php echo $id; ?>">
            <div class="btn-toolbar">
                <div class="btn-group">
                    <select name="filter_client_id" onchange="uaFilter<?php echo $id;?>()" class="input-medium">
                        <?php echo JHtml::_('select.options', $model->getLocations(), 'value', 'text', (int) $params->get('filter_client_id')); ?>
                    </select>
                </div>
                <?php
                    $ext = $params->get('filter_extension');
                    if ($ext_empty) : ?>
                    <div class="btn-group">
                        <select name="filter_extension" onchange="uaFilter<?php echo $id;?>()" class="input-medium">
                            <option value=""><?php echo JText::_('MOD_USERACTIVITY_ADMIN_FIELD_OPTION_SELECT_EXTENSION'); ?></option>
                            <?php echo JHtml::_('select.options', $model->getExtensions(), 'value', 'text', $ext); ?>
                        </select>
                    </div>
                <?php endif; ?>
                <div class="btn-group">
                    <select name="filter_event_id" onchange="uaFilter<?php echo $id;?>()" class="input-medium">
                        <option value=""><?php echo JText::_('MOD_USERACTIVITY_ADMIN_FIELD_OPTION_SELECT_EVENT'); ?></option>
                        <?php echo JHtml::_('select.options', $model->getEvents(), 'value', 'text', $params->get('filter_event_id')); ?>
                    </select>
                </div>
            </div>
            <div class="clearfix"></div>
        </div>
        <!-- End Filters -->
    <?php endif; ?>

    <!-- Start List -->
    	<?php if ($count) : ?>
    		<div id="activities-<?php echo $id; ?>" class="row-striped">
                <?php
                foreach ($data['items'] as $i => $item) :
                    $date = JHtml::_('date', $item->created, $date_format);
                    ?>
                    <div class="row-fluid">
                        <div class="span12">

                            <span class="small muted pull-right">
                                <?php
                                    if ($date_rel) :
                                        ?>
                                        <span class="hasTooltip" title="<?php echo $date; ?>" style="cursor: help;">
                                            <?php echo UserActivityHelper::relativeDateTime($item->created); ?>
                                        </span>
                                        <?php
                                    else :
                                        ?>
                                        <?php echo $date; ?>
                                        <?php
                                    endif;
                                ?>
                            </span>
                            <span class="label label-<?php echo $item->name; ?>"><?php echo $item->name; ?></span>
                            <strong class="row-title"><?php echo $item->text; ?></strong>
                        </div>
                    </div>
                    <?php
                endforeach;
                ?>
            </div>
    	<?php else : ?>
    		<div class="row-fluid">
    			<div class="span12">
    				<div class="alert"><?php echo JText::_('MOD_USERACTIVITY_ADMIN_NO_MATCHING_RESULTS');?></div>
    			</div>
    		</div>
    	<?php endif; ?>
    <!-- End List -->

    <!-- Start Bottom Navigation -->
    <?php if ($count) : ?>
    	<div class="btn-toolbar">
            <div class="btn-group">
                <a class="actbtn-prev-<?php echo $id; ?> btn btn-mini disabled"
                    style="cursor: pointer;" onclick="uaPrev<?php echo $id; ?>(this);"
                >
                    <i class="icon-arrow-up"></i>
                </a>
                <a class="actbtn-next-<?php echo $id; ?> btn btn-mini<?php if ($limit >=  $data['total']) echo ' disabled'; ?>"
                    style="cursor: pointer; " onclick="uaNext<?php echo $id; ?>(this);"
                >
                    <i class="icon-arrow-down"></i>
                </a>
            </div>
    	</div>
        <div class="clearfix"></div>
    <?php endif; ?>
    <!-- End Bottom Navigation -->

    <input type="hidden" name="id" value="<?php echo $id; ?>"/>
    <input type="hidden" value="<?php echo $start; ?>" name="limitstart"/>
    <input type="hidden" value="<?php echo $limit; ?>" name="limit"/>
    <input type="hidden" value="<?php echo $data['total']; ?>" name="total"/>
    <input type="hidden" value="0" name="busy"/>
    <?php echo JHtml::_('form.token'); ?>
</form>
