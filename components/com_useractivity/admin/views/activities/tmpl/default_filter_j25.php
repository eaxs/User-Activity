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

$list_order  = $this->escape($this->state->get('list.ordering'));
$list_dir    = $this->escape($this->state->get('list.direction'));
$save_order  = ($list_order == 'a.ordering');
$sort_fields = $this->getSortFields();
?>
<fieldset id="filter-bar" class="btn-toolbar">
    <div class="filter-search fltlft">
        <div class="fltlft">
        	<label class="filter-search-lbl" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
            <input type="text" name="filter_search" id="filter_search"
                placeholder="<?php echo JText::_('COM_USERACTIVITY_FILTER_SEARCH_DESC'); ?>"
                value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
                title="<?php echo JText::_('COM_USERACTIVITY_FILTER_SEARCH_DESC'); ?>"
            />
        </div>
        <div class="fltlft">
        	<button type="submit" class="btn hasTooltip" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>">
                <?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>
            </button>
        	<button type="button" class="btn hasTooltip" onclick="document.id('filter_search').value='';this.form.submit();" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>">
                <?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>
            </button>
        </div>
    </div>

    <div class="filter-select fltrt btn-toolbar pull-right">
    	<div class="fltrt">
            <select name="filter_published" class="inputbox" onchange="this.form.submit()">
                <?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true);?>
            </select>
        </div>
        <div class="fltrt">
            <select name="filter_client_id" class="inputbox" onchange="this.form.submit()">
                <?php echo JHtml::_('select.options', $this->locations, 'value', 'text', $this->state->get('filter.client_id'), true);?>
            </select>
        </div>
        <div class="fltrt">
            <select name="filter_access" class="inputbox" onchange="this.form.submit()">
                <option value=""><?php echo JText::_('JOPTION_SELECT_ACCESS'); ?></option>
                <?php echo JHtml::_('select.options', JHtml::_('access.assetgroups'), 'value', 'text', $this->state->get('filter.access'), true);?>
            </select>
        </div>
        <div class="fltrt">
            <select name="filter_event_id" class="inputbox" onchange="this.form.submit()">
                <option value=""><?php echo JText::_('JOPTION_SELECT_EVENT'); ?></option>
                <?php echo JHtml::_('select.options', $this->events, 'value', 'text', $this->state->get('filter.event_id'), true);?>
            </select>
        </div>
        <div class="fltrt">
            <select name="filter_extension" class="inputbox" onchange="this.form.submit()">
                <option value=""><?php echo JText::_('JOPTION_SELECT_EXTENSION'); ?></option>
                <?php echo JHtml::_('select.options', $this->extensions, 'value', 'text', $this->state->get('filter.extension'), true);?>
            </select>
        </div>
    </div>
</fieldset>
<div class="clr"></div>