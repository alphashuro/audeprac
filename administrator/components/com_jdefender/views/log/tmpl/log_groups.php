<?php
/**
 * $Id: log_groups.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined ('_JEXEC') or die('Restricted Access');
?>
<form name="adminForm" method="post">
	<table class="adminlist" width='100%'>
		<tr>
			<td valign="top">
				<table class="adminlist" width='800px'>
					<thead>
					<tr>
						<th width="5" nowrap="nowrap">#</th>
						<th width="5" nowrap="nowrap">&nbsp;</th>
						<th width="1%" nowrap="nowrap">
							<?php echo JHTML::_('grid.sort',  'Type', 'type', $this->lists['order_Dir'], $this->lists['order'] ); ?>
						</th>
						<th width="100" nowrap="nowrap">
							<?php echo JHTML::_('grid.sort',  'Number of occurences', 'total', $this->lists['order_Dir'], $this->lists['order'] ); ?>
						</th>
						<th class="title">
							<?php echo JText::_('Description'); ?>
						</th>
						<th width="1%" nowrap="nowrap">
							<?php echo JHTML::_('grid.sort',  'Last Detected', 'ctime', $this->lists['order_Dir'], $this->lists['order'] ); ?>
						</th>
					</tr>
					</thead>
				
					<?php
					$i = 1;
					$k = 0;
					foreach ($this->items as $item){
					?>
						<tr class="<?php print "row$k";?>">
							<td nowrap="nowrap">
								<?php echo $i; ?>
							</td>
							<td nowrap="nowrap" align="center">
								<input type="radio" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $item->id; ?>" onclick="isChecked(this.checked);" />
							</td>
							<td nowrap="nowrap">
								<span class="editLink hasTip" title="<?php echo JText::_('Show detailed log'), '::', $item->title; ?>">
									<a href="<?php echo $item->editLink; ?>"><b><?php echo $item->title; ?></b></a>
								</span>
							</td>
							<td nowrap="nowrap" align="center">
								<b><?php echo $item->total; ?></b>
							</td>
							<td nowrap="nowrap" align="left">
								<?php echo $item->description; ?>
							</td>
							<td nowrap="nowrap">
								<?php echo $item->ctime; ?>
							</td>
						</tr>
					<?php
					$i++;
					$k = 1 - $k;
					}
					?>
				</table>
			</td>
		</tr>
	</table>
	<input type="hidden" name="option" value="<?php echo $option;?>" />
	<input type="hidden" name="controller" value="log" />  
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" /> 
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" /> 
	<input	type="hidden" name="filter_order_Dir" value="" />
</form>