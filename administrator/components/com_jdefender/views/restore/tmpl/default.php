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

<table width='100%' border='0'>
  <tr>
    <td width='150' valign='top'>
  <?php require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'views'.DS.'menu.php');?>
    </td>

    <td valign='top'>

      <form action = 'index.php' method='post' name = 'adminForm' id='adminForm'>
      <table class="adminlist" width='100%'>

      <thead>
      <tr>
          <th width = '1%'>
            #
          </th>
          <th width = '1%'>
            <input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->restorations); ?>);" />
          </th>
          <th align='left' style="text-align:left;">
            <?php print JText::_( 'Name' ); ?>
          </th>
          <th width="1%" nowrap="nowrap">
            <?php print JText::_( 'Size' );?>
          </th>
          <th width="1%" nowrap="nowrap">
            <?php print JText::_( 'Date' );?>
          </th>
          <th width="1%" nowrap="nowrap">
             <?php print JText::_( 'Type' );?>
          </th>
        </tr>
      </thead>

      <?php
      $k=0;
      $i = 0;
      foreach($this->restorations as $file)
      {
      ?>
      <tr class="<?php print "row$k";?>">
        <td width='1%'>
         <?php echo $this->pagination->getRowOffset( $i ); ?>
        </td>
        <td width="1%">
        <?php echo JHTML::_('grid.id', $i, $file['fullname'] );?>
        </td>
        <td align='left'>
        <?php $path = 'backups/'.$file['fullname']; ?>
          <a href='<?php print $path;?>'><?php print JText::_($file['name']);?></a>
        </td>
        <td align='left' nowrap="nowrap">
          <?php print JText::_($file['size']);?>
        </td>
        <td align='left' nowrap="nowrap">
          <?php print JText::_(date("d M Y", $file['time']));?>
        </td>
        <td align='left' nowrap="nowrap">
          <?php print JText::_($file['type']);?>
        </td>
        </tr>
      <?php
      $k = 1 - $k;
      $i++;
      }
      ?>
    </table>
    <input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="controller" value="restore" />
    </form>
    </td>
  </tr>
</table>
