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
JHtml::_('behavior.multiselect');

if (!$this->is_j25) {
    JHtml::_('formbehavior.chosen', 'select');
}

$list_order = $this->escape($this->state->get('list.ordering'));
$list_dir   = $this->escape($this->state->get('list.direction'));
$can_change = $this->user->authorise('core.edit.state', 'com_useractivity');

$date_format = $this->params->get('date_format', JText::_('DATE_FORMAT_LC1'));
$count = sizeOf($this->items);
?>
<?php if (!$this->is_j25) : ?>
    <script type="text/javascript">
    Joomla.orderTable = function()
    {
    	table     = document.getElementById("sortTable");
    	direction = document.getElementById("directionTable");
    	order     = table.options[table.selectedIndex].value;

    	if (order != '<?php echo $list_order; ?>') {
    		dirn = 'desc';
    	} else {
    		dirn = direction.options[direction.selectedIndex].value;
    	}

    	Joomla.tableOrdering(order, dirn, '');
    }
    </script>
<?php endif; ?>
<form action="<?php echo JRoute::_('index.php?option=com_useractivity&view=activities'); ?>" method="post" name="adminForm" id="adminForm">
    <?php
    if (!$this->is_j25) :
        if (!empty($this->sidebar)) :
        ?>
               <div id="j-sidebar-container" class="span2">
                  <?php echo $this->sidebar; ?>
               </div>
               <div id="j-main-container" class="span10">
        <?php else : ?>
               <div id="j-main-container">
        <?php
        endif;
    endif;

    echo $this->loadTemplate('filter_' . ($this->is_j25 ? 'j25' : 'j30'));
    ?>

    <table class="adminlist table table-striped">
        <thead>
            <th width="1%" class="hidden-phone">
                <input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
            </th>
            <th width="1%" style="min-width:55px" class="nowrap center">
                <?php echo JText::_('JSTATUS'); ?>
            </th>
            <th>
                <?php echo JText::_('COM_USERACTIVITY_HEADING_ACTIVITY'); ?>
            </th>
            <th width="20%" class="nowrap">
                <?php echo JHtml::_('grid.sort', 'JDATE', 'a.created', $list_dir, $list_order); ?>
            </th>
            <th width="10%" class="hidden-phone">
                <?php echo JText::_('COM_USERACTIVITY_HEADING_LOCATION'); ?>
            </th>
            <th width="1%" class="nowrap hidden-phone">
                <?php echo JText::_('JGRID_HEADING_ID'); ?>
            </th>
        </thead>
        <tbody>
            <?php
                foreach ($this->items as $i => $item) :
                    ?>
                    <tr class="row<?php echo $i % 2; ?>">
                        <td class="hidden-phone">
                            <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                        </td>
                        <td class="center">
                            <?php echo JHtml::_('jgrid.published', $item->state, $i, 'activities.', $can_change, 'cb'); ?>
                        </td>
                        <td>
                            <?php echo $item->text; ?>
                        </td>
                        <td class="nowrap">
                            <?php echo JHtml::_('date', $item->created, $date_format); ?>
                        </td>
                        <td class="hidden-phone small">
                            <?php echo $item->client; ?>
                        </td>
                        <td class="hidden-phone small">
                            <?php echo (int) $item->id; ?>
                        </td>
                    </tr>
                <?php
                endforeach;
            ?>
        </tbody>
        <?php if ($this->is_j25) : ?>
            <tfoot>
                <tr>
                    <td colspan="6">
                        <?php echo $this->pagination->getListFooter(); ?>
                    </td>
                </tr>
            </tfoot>
        <?php endif; ?>
    </table>

    <?php if (!$this->is_j25) echo $this->pagination->getListFooter(); ?>

    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="filter_order" value="<?php echo $list_order; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $list_dir; ?>" />
    <?php echo JHtml::_('form.token'); ?>

    <?php if (!$this->is_j25) : ?>
        </div>
    <?php endif; ?>
</form>
