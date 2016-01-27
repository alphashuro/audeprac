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
defined("_JEXEC") or die("Restricted Access");
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
				
					xajax_jdPublishBlock(i.rel);

					if (prev == '<?php echo JURI::base(); ?>images/tick.png')
						img.setAttribute('src', '<?php echo JURI::base(); ?>images/publish_x.png');
					else
						img.setAttribute('src', '<?php echo JURI::base(); ?>images/tick.png');
				}
			});
		});
	});
</script>
  <form action='index.php' method='post' name='adminForm'>
  	<div>
  		<div style="float:right">
  			<?php echo $this->lists['type']; ?>
  			<?php echo $this->lists['published']; ?>
  		</div>
  		<br />
  		<br />
  	</div>
    <table class="adminlist" width='100%' align='left'>
		<thead>
		<tr>
		<th width='1%'> # </th>
          <th width='1%'>
            <input type="checkbox"  name="toggle" value="" onclick="checkAll(<?php echo count( $this->items ); ?>);"/>
          </th>
          <th width="100" nowrap="nowrap">
            <?php echo JHTML::_('grid.sort',  'Type', 'type', $this->lists['order_Dir'], $this->lists['order'] ); ?>
          </th>
          <th width="100" nowrap="nowrap">
            <?php echo JHTML::_('grid.sort',  'Value', 'value', $this->lists['order_Dir'], $this->lists['order'] ); ?>
          </th>
          <th width="100" nowrap="nowrap">
            <?php echo JHTML::_('grid.sort',  'Reason', 'reason', $this->lists['order_Dir'], $this->lists['order'] ); ?>
          </th>
          <th width="10%" nowrap="nowrap">
            <?php echo JHTML::_('grid.sort',  'Date', 'ctime', $this->lists['order_Dir'], $this->lists['order'] ); ?>
          </th>
          <th width="1%" nowrap="nowrap">
            <?php echo JHTML::_('grid.sort',  'Published', 'published', $this->lists['order_Dir'], $this->lists['order'] ); ?>
          </th>
        </tr>
        </thead>
		<tfoot>
	      	<tr>
				<td colspan='7' align='center'>
	            <?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>

      <?php
      $i = 0;
      foreach ($this->items as $item) :
      ?>
      <tr class="row<?php echo $i % 2; ?>">
        <td>
        <?php echo $this->pagination->getRowOffset( $i ); ?>
        </td>
        <td>
        <?php echo JHTML::_('grid.id', $i, $item->id );?>
        </td>
        <td align="center">
         <?php print JText::_($item->type); ?>
        </td>
        <td align="center">
        	<span title="<?php echo JText::_('Edit'); ?>" class="hasTip">
	        	<?php echo JHTML::link('index.php?option=com_jdefender&controller=block&task=add&cid[]='.$item->id, $item->value); ?>
        	</span>
        </td>
        <td align="center">
         <?php print JText::_($item->reason); ?>
        </td>
        <td align="center">
         <?php print $item->ctime; ?>
        </td>
        <td align="center" width="1%">
         <?php
			$img = $item->published ? $imgY = 'tick.png' : $imgX = 'publish_x.png';
			
			$alt = $item->published ? JText::_('Published') : JText::_('Unpublished');
			$action = $item->published ? JText::_('Unpublish Item') : JText::_('Publish item');
			
			echo '<span><a class="publish_button" rel="', $item->id, '" href="javascript:void(0);"><img src="images/', $img, '" border="0" align="absmiddle" alt="', $alt, '"/></a><span>';
         ?>
        </td>
      </tr>

      <?php
      $i++;
      endforeach;
      ?>
    </table>
    
    <input type="hidden" name="option" value="<?php echo $option;?>" />
    <input type='hidden' name='controller' value='block'>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
		<input type="hidden" name="filter_order_Dir" value="" />
   </form>