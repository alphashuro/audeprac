<?php
/**
 * $Id: default.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined('_JEXEC') or die('Restricted Access');

?>
<script>
	window.addEvent('domready', function() {
		$$('a.publish_button').each(function( i ) {
			i.addEvent('click', function() {
				var img = $E('img', i);

				if (img) 
				{
					var prev = img.getAttribute('src');					
					
					img.setAttribute('src', '<?php echo JURI::base(); ?>components/com_jdefender/images/load.gif');
				
					xajax_jdPublishRule(i.rel);

					if (prev == '<?php echo JURI::base(); ?>images/tick.png')
						img.setAttribute('src', '<?php echo JURI::base(); ?>images/publish_x.png');
					else
						img.setAttribute('src', '<?php echo JURI::base(); ?>images/tick.png');
				}
			});
		});
	});
</script>
<style>
table.card {
	background-color: #f9f9f9;
	border: 1px solid #dddddd;
	width: 30%;
}

table.card td {
	border-bottom: 1px solid #dddddd;
}
</style>

<form action="" method='post' name='adminForm'>

<table width="100%">
	<tr>
		<td colspan="2">
			<div style="float: left">
				<?php echo JText::_('Filter'); ?>: 
				<input type="text" name="search" id="search" value="<?php echo $this->lists['search']; ?>" class="text_area" onchange="submitform();" title="<?php echo JText::_('Filter log records'); ?>"/>
				<button onclick="submitform();"><?php echo JText::_('Go'); ?></button>
				<button onclick="document.getElementById('search').value='';submitform();"><?php echo JText::_('Reset'); ?></button>
			</div>
			<div style="float:right">
				<?php echo $this->lists['state']; ?>
			</div>
			<div style="clear:both;height:1px"></div>
		</td>
	</tr>
	<tr>
		<td valign='top'>
		<table class="adminlist" width='100%'>
			<tfoot>
				<tr>
					<td colspan="12" align="center">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
			<thead>
			<tr>
				<th width="5">
					#
				</th>
				<th width="5">
					<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->items ); ?>);" />
				</th>
				<th width="1%">
					<?php echo JHTML::_('grid.sort', 'Verdict', 'r.family', $this->lists['order_Dir'], $this->lists['order'] ); ?>
				</th>
				<th width="1%" nowrap="nowrap">
					<?php echo JHTML::_('grid.sort', 'Component', 'r.type', $this->lists['order_Dir'], $this->lists['order'] ); ?>
				</th>
				<th nowrap="nowrap" width="1%">
					<?php echo JHTML::_('grid.sort', 'Variable', 'r.variable', $this->lists['order_Dir'], $this->lists['order'] ); ?>
				</th>
				<th align="center">
					<?php echo JText::_('Action'); ?>
				</th>
				<th width="1%">
					<?php echo JText::_('Origin'); ?>
				</th>
				<th width="1%">
					<?php echo JHTML::_('grid.sort', 'Type', 'r.type', $this->lists['order_Dir'], $this->lists['order'] ); ?>
				</th>
				<th width="1%">
					<?php echo JText::_('Version'); ?>
				</th>
				<th width="1%" nowrap="nowrap">
					<?php echo JHTML::_('grid.sort',  'Date', 'r.ctime', $this->lists['order_Dir'], $this->lists['order'] ); ?>
				</th>
				<th width="1%" nowrap="nowrap">
					<?php echo JHTML::_('grid.sort',  'Published', 'r.published', $this->lists['order_Dir'], $this->lists['order'] ); ?>
				</th>
				<th width="1%" nowrap="nowrap">
					<?php echo JHTML::_('grid.sort',  'ID', 'r.id', $this->lists['order_Dir'], $this->lists['order'] ); ?>
				</th>
			</tr>
			</thead>

			<?php
			$i = 0;
			$k = 1;
			?>
			<?php foreach ($this->items as $item): ?>
			<?php
				$k = 1 - $k;
				$i++; 
			?>
			<tr class="<?php print "row$k";?>">
				<td nowrap="nowrap">
					<?php echo $this->pagination->getRowOffset( $i-1 ); ?>
				</td>
				<td nowrap="nowrap">
					<?php echo JHTML::_('grid.id', $i-1, $item->id );?>
				</td>
				<td nowrap="nowrap">
					<?php print $this->escape(JText::_($item->familyTitle));?>
				</td>
				<td align="center" nowrap="nowrap">
					<?php 
						if ($item->component == '*') {
							$item->component = '<span class="defender_highlight">'.JText::_('All components').'</span>';
						}
					?>
					<?php print $item->component;?>
				</td>
				<td nowrap="nowrap" align="center">
					<?php 
						if ($item->variable == '*') {
							$item->variable = '<span class="defender_highlight">'.JText::_('All variables').'</span>';
						}
					?>
					<?php print JText::_($item->variable);?>
				</td>
				<td nowrap="nowrap" align="center">
					<span class="hasTip" title="<?php echo JText::_('View rule'); ?>::<?php echo JText::_('Quick view'); ?>">
						<?php echo $item->viewLink; ?>						
					</span>
					<span class="hasTip" title="<?php echo JText::_('Edit'); ?>::<?php echo JText::_('Edit rule'); ?>">
						<?php echo $item->editLink; ?>						
					</span>
					<span class="hasTip" title="<?php echo JText::_('View rule'); ?>::<?php echo JText::_('Quick view'); ?>">
						<?php print JText::_($item->actionTitle);?>
					</span>
				</td>
				<td nowrap="nowrap" width="1%" align="center">
					<?php print JText::_($item->origin);?>
				</td>
				<td nowrap="nowrap">
					<?php print $this->escape(JText::_($item->type));?>
				</td>
				<td nowrap="nowrap" width="1%" align="center">
					<?php print JText::_($item->version);?>
				</td>
				<td nowrap="nowrap" width="1%">
					<?php print JText::_($item->ctime);?>
				</td>
				<td align="center" width="1%">
		         <?php
					$img = $item->published ? $imgY = 'tick.png' : $imgX = 'publish_x.png';
					
					$alt = $item->published ? JText::_('Published') : JText::_('Unpublished');
					$action = $item->published ? JText::_('Unpublish Item') : JText::_('Publish item');
					
					echo '<span><a class="publish_button" rel="', $item->id, '" href="javascript:void(0);"><img src="', JURI::base(), 'images/', $img, '" border="0" align="absmiddle" alt="', $alt, '"/></a><span>';
		        ?>
		        </td>
				<td nowrap="nowrap" width="1%">
					<?php print JText::_($item->id);?>
				</td>
			</tr>
			<?php endforeach; ?>
		</table>
			<input type="hidden" name="option" value="com_jdefender" /> 
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="view" value="rules" />  
			<input type="hidden" name="boxchecked" value="0" /> 
			<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
			<input type="hidden" name="filter_order_Dir" value="" />
			<?php echo JHTML::_('form.token'); ?>
		</td>
	</tr>
</table>
</form>