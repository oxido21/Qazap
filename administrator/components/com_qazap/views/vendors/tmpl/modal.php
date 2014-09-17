<?php
/**
 * @version     1.0.0
 * @package     com_qazap
 * @copyright   Copyright (C) 2013 VirtuePlanet Services LLP. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      VirtuePlanet Services LLP <info@virtueplanet.com> - http://www.virtueplanet.com
 */

// no direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

// Import CSS
$document = JFactory::getDocument();
$user	= JFactory::getUser();
$userId	= $user->get('id');
$listOrder	= $this->state->get('list.ordering');
$input     = JFactory::getApplication()->input;
$function  = $input->getCmd('function');
$listDirn	= $this->state->get('list.direction');
$canOrder	= $user->authorise('core.edit.state', 'com_qazap');
$saveOrder	= $listOrder == 'a.id';
if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_qazap&task=vendors.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'vendorList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}
$sortFields = $this->getSortFields();
$originalOrders = array();
$states = array(
					1 => array('block', 'COM_QAZAP_ACTIVE', 'COM_QAZAP_BLOCK_VENDOR', 'COM_QAZAP_ACTIVE', true, 'publish', 'publish'),
					0 => array('activate', 'COM_QAZAP_INACTIVE', 'COM_QAZAP_UNBLOCK_VENDOR', 'COM_QAZAP_INACTIVE', true, 'unpublish', 'unpublish')
					);
if (!empty($this->extra_sidebar)) 
{
	$this->sidebar .= $this->extra_sidebar;
}
?>
<script type="text/javascript">
	Joomla.orderTable = function() {
		table = document.getElementById("sortTable");
		direction = document.getElementById("directionTable");
		order = table.options[table.selectedIndex].value;
		if (order != '<?php echo $listOrder; ?>') {
			dirn = 'asc';
		} else {
			dirn = direction.options[direction.selectedIndex].value;
		}
		Joomla.tableOrdering(order, dirn, '');
	}
</script>
<form action="<?php echo JRoute::_('index.php?option=com_qazap&view=vendors'); ?>" method="post" name="adminForm" id="adminForm">    
	<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
		<?php if (empty($this->items)) : ?>
			<div class="alert alert-no-items">
				<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php else : ?>      
		<div class="clearfix"> </div>
		<table class="table table-striped" id="vendorList">
			<thead>
				<tr>
					<?php if (isset($this->items[0]->state)): ?>
					<?php endif; ?>
					<th class="left">
						<?php echo JHtml::_('searchtools.sort',  'COM_QAZAP_VENDORS_SHOP_NAME', 'a.shop_name', $listDirn, $listOrder); ?>
					</th>
					<th class="left">
						<?php echo JHtml::_('searchtools.sort',  'COM_QAZAP_USERNAME', 'u.username', $listDirn, $listOrder); ?>
					</th>
					<th width="1%" class="nowrap center">
						<?php echo JHtml::_('searchtools.sort', 'COM_QAZAP_ACTIVATION_STATE', 'a.state', $listDirn, $listOrder); ?>
					</th>	
					<th width="1%" class="nowrap center">
						<?php echo JHtml::_('searchtools.sort', 'COM_QAZAP_VENDORS_PRODUCT_COUNT', 'product_count', $listDirn, $listOrder); ?>
					</th>						
					<th class="center">
						<?php echo JHtml::_('searchtools.sort',  'COM_QAZAP_VENDOR_GROUP', 'g.title', $listDirn, $listOrder); ?>
					</th>										
					<th class="left">
						<?php echo JHtml::_('searchtools.sort',  'COM_QAZAP_EMAIL', 'u.email', $listDirn, $listOrder); ?>
					</th>											
					<th class="left">
						<?php echo JHtml::_('searchtools.sort', 'COM_QAZAP_VENDORS_CREATED_BY', 'auth.name', $listDirn, $listOrder); ?>
					</th>
					<th class="center">
						<?php echo JHtml::_('searchtools.sort', 'COM_QAZAP_CREATED_ON', 'a.created_time', $listDirn, $listOrder); ?>
					</th>									
					<th width="1%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
			<?php if(isset($this->items[0]))
			{
				$colspan = count(get_object_vars($this->items[0]));
			}
			else
			{
				$colspan = 15;
			} ?>
			<tr>
				<td colspan="<?php echo $colspan ?>">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
			</tfoot>
			<tbody>
			<?php foreach ($this->items as $i => $item) :
				$ordering   = ($listOrder == 'a.id');
        $canCreate	= $user->authorise('core.create', 'com_qazap');
        $canEdit	= $user->authorise('core.edit', 'com_qazap');
        $canCheckin	= $user->authorise('core.manage', 'com_qazap');
        $canChange	= $user->authorise('core.edit.state', 'com_qazap');
				?>
				<tr class="row<?php echo $i % 2; ?>">
					<td>
					<?php if (isset($item->checked_out) && $item->checked_out) : ?>
						<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'vendors.', $canCheckin); ?>
					<?php endif; ?>
						<a href="javascript:void(0)" onclick="if (window.parent) window.parent.<?php echo $this->escape($function);?>('<?php echo $item->id; ?>', '<?php echo $this->escape(addslashes(($item->shop_name))); ?>', '', null, null, null, null);"><?php echo $this->escape($item->shop_name); ?></a>
					</td>
					<td class="left">
						<?php echo $this->escape($item->username); ?>
					</td>	
					<td class="center">
						<?php echo JHtml::_('jgrid.state', $states, $item->state, $i, 'vendors.', $canChange, true, 'cb'); ?>
					</td>
					<td class="center">
						<?php if($item->product_count > 0) : ?>
						<a href="<?php echo JRoute::_('index.php?option=com_qazap&view=products&vendor_id=' . (int) $item->id) ?>" target="_blank">
							<span class="label label-info"><?php echo (int) $item->product_count; ?></span>
						</a>
						<?php else : ?>
						<span class="label"><?php echo (int) $item->product_count; ?></span>
						<?php endif; ?>
					</td>						
					<td class="center small">
						<?php echo $this->escape($item->vendor_group_name); ?>
					</td>												
					<td class="left small">
						<?php echo $this->escape($item->juser_email); ?>
					</td>										
					<td class="left small">
						<?php echo $this->escape($item->author); ?>
					</td>
					<td class="center small">
						<?php echo JHtml::_('date', $item->created_time, 'Y-m-d H:i:s'); ?>
					</td>					
					<td class="center hidden-phone">
						<?php echo (int) $item->id; ?>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif;?>
	</div>
	
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
</form>        

		
       

		
