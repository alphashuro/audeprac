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

<form action="<?php echo $this->request_url; ?>" method='post' name='adminForm'>

<table width="100%">
	<tr>
		<td colspan="2">
			<div style="float: left">
				<?php echo JText::_('Filter'); ?>: 
				<input type="text" name="filter_search" id="search" value="<?php echo $this->lists['search']; ?>" class="text_area" onchange="submitform();" title="<?php echo JText::_('Filter log records'); ?>"/>
				<button onclick="submitform();"><?php echo JText::_('Go'); ?></button>
				<button onclick="document.getElementById('search').value='';submitform();"><?php echo JText::_('Reset'); ?></button>
			</div>
			<div style="float:right">
				<?php echo $this->lists['quickjump']; ?>	<?php echo $this->lists['extension']; ?> <?php echo $this->lists['state']; ?>
			</div>
			<div style="clear:both;height:1px"></div>
		</td>
	</tr>
	<tr>
		<td valign='top'>
		<table class="adminlist" width='100%'>
			<thead>
			<tr>
				<th width="5">
					#
				</th>
				<th width="5">
					<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->items ); ?>);" />
				</th>
				<th width="1%">
					<?php echo JHTML::_('grid.sort',  'State', 'l.status', $this->lists['order_Dir'], $this->lists['order'] ); ?>
				</th>
				<?php if (empty($this->logType)) : ?>
				<th width="1%" nowrap="nowrap">
					<?php echo JHTML::_('grid.sort',  'Type', 'l.type', $this->lists['order_Dir'], $this->lists['order'] ); ?>
				</th>
				<?php endif; ?>
				<th nowrap="nowrap">
					<?php echo JText::_('Source', 'l.url', $this->lists['order_Dir'], $this->lists['order'] ); ?>
				</th>
				<th width="30%">
					<?php echo JText::_('Issue'); ?>
				</th>
				<?php if ($this->showTotal) : ?>
				<th width="1%">
					<?php echo JText::_('Count', 'l.`total`', $this->lists['order_Dir'], $this->lists['order'] ); ?>
				</th>
				<?php endif; ?>
				<th width="1%" nowrap="nowrap">
					<?php echo JHTML::_('grid.sort',  'Date', 'l.ctime', $this->lists['order_Dir'], $this->lists['order'] ); ?>
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
					<?php print $this->escape(JText::_($item->status));?>
				</td>
				<?php if (empty($this->logType)) : ?>
				<td nowrap="nowrap">
					<?php echo $item->title; ?>
				</td>
				<?php endif; ?>
				<td nowrap="nowrap">
					<?php
					$image = $item->blocked_ip ? 'locked.gif' : 'unlocked.gif';
					?> 
					<?php if ($item->ip) : ?>
					<span class="hasTip" title="<?php echo JText::_('Quick Block/Unblock IP'); ?>">
						<a href='javascript:void(0);' onClick="changeImageAll('<?php print $item->ip;?>', '<?php print $i;?>'); xajax_jdBlockParam('<?php print $item->ip;?>', 'ip','<?php print $item->ip;?>'); ">
							<img src='components/com_jdefender/images/<?php print $image;?>' id='<?php print $i.'img_block';?>'>
						</a>
					</span>
					<?php endif; ?>
					<?php print $item->source;?>
					<input type='hidden' id='<?php print $i."hidden_ip";?>' value='<?php print $item->ip;?>' />
				</td>
				<td align="center">
					<?php print nl2br($this->escape($item->issue));?>
				</td>
				<?php if ($this->showTotal) : ?>
					<td align="center">
						<?php echo $item->total; ?>
					</td>
				<?php endif; ?>
				<td nowrap="nowrap">
					<?php print JText::_($item->ctime);?>
				</td>
			</tr>
			<?php endforeach; ?>
			<tfoot>
			<tr>
				<td colspan='8' align='center'><?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
			</tfoot>
		</table>
			<input type="hidden" name="option" value="com_jdefender" /> 
			<input type="hidden" name="task" value="showLog" />
			<input type="hidden" name="view" value="log" />  
			<input type="hidden" name="boxchecked" value="0" /> 
			<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
			<input type="hidden" name="filter_order_Dir" value="" />
			<?php foreach ($this->types as $type) : ?>
				<input type="hidden" name="types[]" value="<?php echo $type; ?>" />
			<?php endforeach; ?>
			<?php echo JHTML::_('form.token'); ?>

		</td>
	</tr>
</table>
</form>

<script language="javascript">
function JDchangeImage(img_id)
{
  document.getElementById(img_id).src='components/com_jdefender/images/load.gif';
}


function changeImageAll(ip,action){
      var limit=<?php print $this->total;?>;
      
      if (document.getElementById(action+''+"img_block").src=='<?php print JURI::base(); ?>components/com_jdefender/images/locked.gif'){

       for (var i=1; i<=limit; i++){
          if (document.getElementById(i+"hidden_ip").value==ip){
            document.getElementById(i+''+"img_block").src='components/com_jdefender/images/unlocked.gif';
          }
        }
      }

      else{
          for (var i=1; i<=limit ;i++){
          if (document.getElementById(i+''+"hidden_ip").value==ip){
            document.getElementById(i+''+"img_block").src='components/com_jdefender/images/locked.gif';
          }
        }
      }

      }
</script>