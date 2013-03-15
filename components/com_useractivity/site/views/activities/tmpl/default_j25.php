<?php
/**
 * @package      User Activity
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


JHtml::_('behavior.tooltip');

$list_order  = $this->escape($this->state->get('list.ordering'));
$list_dir    = $this->escape($this->state->get('list.direction'));
$date_format = $this->params->get('date_format', JText::_('DATE_FORMAT_LC1'));
$date_rel    = (int) $this->params->get('date_relative', 1);
?>
<form action="<?php echo JRoute::_(UserActivityHelperRoute::getActivitiesRoute()); ?>" method="post" name="adminForm" id="adminForm" autocomplete="off">

    <!-- Start Filters -->
    <fieldset class="filters">
        <?php if ($this->params->get('show_filter_search')) : ?>
            <input type="text" class="inputbox" placeholder="<?php echo JText::_('COM_USERACTIVITY_FILTER_SEARCH_DESC'); ?>"
                name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
                title="<?php echo JText::_('COM_USERACTIVITY_FILTER_SEARCH_DESC'); ?>"
            />
            <button class="button" type="submit">
                <?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>
            </button>
            <button class="button" type="button" onclick="document.id('filter_search').value='';this.form.submit();">
                <?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>
            </button>
        <?php endif; ?>
        <?php if ($this->params->get('show_filter_extension')) : ?>
            <select name="filter_extension" onchange="this.form.submit();" class="inputbox">
                <option value=""><?php echo JText::_('JOPTION_SELECT_EXTENSION'); ?></option>
                <?php echo JHtml::_('select.options', $this->extensions, 'value', 'text', $this->state->get('filter.extension')); ?>
            </select>
        <?php endif; ?>
        <?php if ($this->params->get('show_filter_event')) : ?>
            <select name="filter_event_id" onchange="this.form.submit();" class="inputbox">
                <option value=""><?php echo JText::_('JOPTION_SELECT_EVENT'); ?></option>
                <?php echo JHtml::_('select.options', $this->events, 'value', 'text', $this->state->get('filter.event_id')); ?>
            </select>
        <?php endif; ?>
    </fieldset>
    <!-- End Filters -->

    <!-- Start Items -->
    <ul>
        <?php
        foreach ($this->items as $i => $item) :
            $date = JHtml::_('date', $item->created, $date_format);
            ?>
            <li>
                <span class="row-title"><?php echo $item->text; ?></span>
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
    </ul>
    <!-- End Items -->

    <?php echo $this->pagination->getListFooter(); ?>

    <input type="hidden" name="task" value="" />
    <input type="hidden" name="filter_order" value="<?php echo $list_order; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $list_dir; ?>" />
    <?php echo JHtml::_('form.token'); ?>
</form>