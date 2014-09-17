<?php
/**
 * multimodal.php
 *
 * LICENSE: Qazap is a free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or is 
 * derivative of works licensed under the GNU General Public License or other free
 * or open source software licenses.
 *
 * @package    Qazap
 * @subpackage Admin
 * @author     Abhishek Das <abhishek@virtueplanet.com>
 * @copyright  Copyright (C) 2014. VirtuePlanet Services LLP. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @version    SVN: $Id$
 * @link       http://www.qazap.com/download
 * @since      File available since Release 1.0.0
 */
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');
$app = JFactory::getApplication();
if ($app->isSite())
{
	JSession::checkToken('get') or die(JText::_('JINVALID_TOKEN'));
}

$function  = $app->input->getCmd('function', 'jSelectCategory');
/**
* 
* GET CURRENT LANGUAGE
* 
*/
$lang = JFactory::getLanguage();
$present_language = $lang->getTag();

$user	= JFactory::getUser();
$userId	= $user->get('id');
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$ordering 	= ($listOrder == 'a.lft');
$canOrder	= $user->authorise('core.edit.state', 'com_qazap');
$saveOrder 	= ($listOrder == 'a.lft' && strtolower($listDirn) == 'asc');
$sortFields = $this->getSortFields();
$originalOrders = array();
?>

<form action="<?php echo JRoute::_('index.php?option=com_qazap&view=categories'); ?>" method="post" name="adminForm" id="adminForm">
<div>
    	<?php
		// Search tools bar
		echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
		?>
		<?php if (empty($this->items)) : ?>
			<div class="alert alert-no-items">
				<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php else : ?>
		<table class="table table-striped" id="categoryList">
			<thead>
				<tr>
					<th width="1%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('searchtools.sort', '', 'a.lft', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
					</th>
					<th width="1%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.checkall'); ?>
					</th>				
					<th width="1%" class="nowrap center">
						<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
					</th>
					<th>
						<?php echo JHtml::_('searchtools.sort', 'JGLOBAL_TITLE', 'b.title', $listDirn, $listOrder); ?>
					</th>
					<th>
						<?php echo JHtml::_('searchtools.sort', 'JGLOBAL_TITLE', 'b.title', $listDirn, $listOrder); ?>
					</th>
					<th width="10%" class="nowrap hidden-phone">
						<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'a.access', $listDirn, $listOrder); ?>
					</th>						
					<th width="1%" class="nowrap center">
						<?php //echo JHtml::_('searchtools.sort', 'COM_QAZAP_CATEGORY_PRODUCT_COUNT', 'product_count', $listDirn, $listOrder); ?>
						<?php echo JText::_('COM_QAZAP_CATEGORY_PRODUCT_COUNT') ?>
					</th>
					<th width="1%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.category_id', $listDirn, $listOrder); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
			<?php 
			if(isset($this->items[0]))
			{
				$colspan = count(get_object_vars($this->items[0]));
			}
			else
			{
				$colspan = 10;
			}
			?>
			<tr>
				<td colspan="<?php echo $colspan ?>">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
			</tfoot>
			<tbody>
			<?php foreach ($this->items as $i => $item) :
				$orderkey   = array_search($item->category_id, $this->ordering[$item->parent_id]);				
				$canEdit    = $user->authorise('core.edit', 'com_qazap.category.' . $item->category_id);
				$canCheckin = $user->authorise('core.admin', 'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
				$canEditOwn = $user->authorise('core.edit.own', 'com_qazap.category.' . $item->category_id) && $item->created_user_id == $userId;
				$canChange  = $user->authorise('core.edit.state', 'com_qazap.category.' . $item->category_id) && $canCheckin;		
				// Get the parents of item for sorting
				if ($item->level > 1)
				{
					$parentsStr = "";
					$_currentParentId = $item->parent_id;
					$parentsStr = " " . $_currentParentId;
					for ($i2 = 0; $i2 < $item->level; $i2++)
					{
						foreach ($this->ordering as $k => $v)
						{
							$v = implode("-", $v);
							$v = "-" . $v . "-";
							if (strpos($v, "-" . $_currentParentId . "-") !== false)
							{
								$parentsStr .= " " . $k;
								$_currentParentId = $k;
								break;
							}
						}
					}
				}
				else
				{
					$parentsStr = "";
				}				
				?>
				<tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->parent_id; ?>" item-id="<?php echo $item->category_id ?>" parents="<?php echo $parentsStr ?>" level="<?php echo $item->level ?>">
					<td class="order nowrap center hidden-phone">
						<?php
						$iconClass = '';
						if (!$canChange)
						{
							$iconClass = ' inactive';
						}
						elseif (!$saveOrder)
						{
							$iconClass = ' inactive tip-top hasTooltip" title="' . JHtml::tooltipText('JORDERINGDISABLED');
						}
						?>
						<span class="sortable-handler<?php echo $iconClass ?>">
							<i class="icon-menu"></i>
						</span>
						<?php if ($canChange && $saveOrder) : ?>
							<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $orderkey + 1; ?>" />
						<?php endif; ?>
					</td>                   
					<td class="center hidden-phone">
						<?php echo JHtml::_('grid.id', $i, $item->category_id); ?>
					</td>
					<td class="center">
						<?php echo JHtml::_('jgrid.published', $item->published, $i, 'categories.', $canChange); ?>
					</td>
					<td class="qazap_category_name">
						<?php echo $this->escape($item->title); ?>
					</td>
					<td>
						<?php echo str_repeat('<span class="gi">&mdash;</span>', $item->level - 1) ?>
						<?php if ($item->checked_out) : ?>
							<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'categories.', $canCheckin); ?>
						<?php endif; ?>
							<?php echo $this->escape($item->title); ?>

						<span class="small" title="<?php echo $this->escape($item->path); ?>">
							<?php if (empty($item->note)) : ?>
								<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
							<?php else : ?>
								<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS_NOTE', $this->escape($item->alias), $this->escape($item->note)); ?>
							<?php endif; ?>
						</span>
					</td>
					<td class="small hidden-phone">
						<?php echo $this->escape($item->access_level); ?>
					</td>
					<td class="center">
						<?php echo $item->product_count;?>
					</td>
					<td class="center hidden-phone">
						<span title="<?php echo sprintf('%d-%d', $item->lft, $item->rgt); ?>">
							<?php echo (int) $item->category_id; ?></span>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php endif;?>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="original_order_values" value="<?php echo implode($originalOrders, ','); ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
	<button type="button" onclick="if (document.adminForm.boxchecked.value==0){alert('Please first make a selection from the list');} else if(window.parent){ return window.returnParentValues(this);}" class="btn btn-small">
		<span class="icon-publish"></span>Submit
	</button>
</form>        
<?php
$doc = JFactory::getDocument();
$doc->addScriptDeclaration("
function returnParentValues(btn) {
	var form = jq(btn).parents('form');
	var val = [];
	var name = [];
	form.find(':checkbox:checked').each(function(i){
		if(jq(this).val()) {
			name[i] = jq(this).parents('tr').find('.qazap_category_name').text();
			name[i] = jq.trim(name[i]);
			val[i] = jq(this).val();			
		}
	});
	if(val.length) {
	  var newArray = new Array();
	  for(var i = 0; i< val.length; i++){
	      if (val[i]){
	        newArray.push(val[i]);
	    }
	  }
	  val = newArray;	
	  
	  var newArray = new Array();
	  for(var i = 0; i< name.length; i++){
	      if (name[i]){
	        newArray.push(name[i]);
	    }
	  }
	  name = newArray;	  	
	}
	if(val.length && name.length) {
		var names = name.join(', ');
		var values = val.join(',');
		if (window.parent) {
			window.parent.".$this->escape($function)."(values, names);
		}				
	}	
	return false;
}
");
?>