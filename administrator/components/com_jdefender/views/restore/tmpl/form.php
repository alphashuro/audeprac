<?php
/**
 * $Id: form.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined("_JEXEC") or die("Restricted Access");
require_once (JPATH_COMPONENT . DS . 'xajax.php');
?>
<form action='index.php' method='post' name='adminForm' id="adminForm">
<table width='100%' class="adminlist">
  <thead>
  <tr>
    <th width="50%" colspan="3" align='center'>
      <b><?php print JText::_( 'Options of the Restoration' );?></b>
    </th>
  </tr>
  </thead>

  <tr>
    <td width='50%' valign="top">
      <div>
        <input type='text' size='20' name='param[name]'> <?php print JText::_( 'Name of the restoration' );?>
        <span style='text-align:right;float:right; margin-top:-15px;'>
        <?php print JText::_( 'Type of backup archive:' );?>
        <select name='param[arc_type]'>
          <!-- <option value='zip'>zip</option> -->
          <option value='bzip2'>bzip2</option>
          <option value='tar'  selected='selected'>tar</option>
          <option value='gzip'>gzip</option>
        </select>
        </span>
      </div>
      <div>
      <input type='checkbox' name="param[drop]" value='1'> <?php print JText::_( 'Add DROP TABLE' );?>
      </div>
      <div>
      <input type='checkbox' name='param[exist]' value='1'> <?php print JText::_( 'Add IF NOT EXISTS' );?>
      </div>
      <div>
      <input type='checkbox' name='param[auto]' value='1'> <?php print JText::_( 'Add AUTO INCREMENT' );?>
      </div>

    </td>
    <td width='25%'>
    <div><?php print JText::_( 'Comments to the Database Backup' );?></div>
    <textarea rows="4" cols='40' name="param[comments]"></textarea>
    </td>
    <td width='25%' valign='top'>
    <?php
    $dirs = array('administrator'.DS, 'administrator'.DS.'backups'.DS );
    foreach ($dirs as $dir){
        if (is_writable(JPATH_ROOT.DS.$dir))
            {
              print "<div>{$dir} <b style='color:green;'>".JText::_('Writable')."</b></div>";
            }
            else
            {
              print "<div>{$dir} <b style='color:red;'>".JText::_('Not Writable')."</b></div>";
            }
    }
    ?>
    </td>
  </tr>

    <thead>
    <tr>
      <th>
        <b><?php print JText::_( 'Select files and folders to backup' );?></b>
      </th>
    <th colspan="2">
    <b><?php print JText::_( 'Select tables to backup' );?></b>
    </th>
    </tr>
    </thead>

    <tr>
    <td >
    <div style='width:100%; overflow:auto; height:300px;'>
    <div id='sel_home' style='margin-left:20px;text-align:left;'><img src='components/com_jdefender/images/icons/folder.png'>
    <input type='checkbox' name='param[home]' value = '1' id='folder[home]'>
       <?php print JText::_('Pack All');?></div>
     <?php print jdGetDirectoryList(JPATH_ROOT);?>
    </div>
    </td>
    <td colspan="2">
    <div style='width:100%; overflow:auto; height:300px;'>
      <table class='adminlist'>

        <thead>
          <tr>
            <th width='1%'>
            <input type='checkbox'
                   name="param[alltables]"
                   value="1"
                   onClick="checkAllTables(<?php print count($this->tablesInfo);?>, 'table_')"
                   id='Tables'>
            </th>
            <th align='left' style='text-align:left;'>
            <b style='text-align:left;'><?php print JText::_( 'Name' );?></b>
            </th>
            <th width='1%' nowrap="nowrap">
            <b><?php print JText::_( 'Rows' );?></b>
            </th>
            <th width='1%' nowrap="nowrap">
            <b><?php print JText::_( 'Size' );?></b>
            </th>
            <th width='1%' nowrap="nowrap">
            <b><?php print JText::_( 'Overhead' );?></b>
            </th>
            <th width='1%' nowrap="nowrap">
            <b><?php print JText::_( 'Auto inc.' );?></b>
            </th>
          </tr>
        </thead>

        <?php
        $k = 0;
        $counter=0;
        foreach ($this->tablesInfo as $table){
        ?>
          <tr class='row<?php print $k;?>'>
          <td>
            <input type='checkbox' name='table[<?php print $counter;?>]' value='<?php print $table['name'];?>' id='table_<?php print $counter;?>'>
          </td>
          <td align='left'>
            <?php print $table['name'];?>
          </td>
          <td nowrap="nowrap" align='center'>
            <?php print $table['rows'];?>
          </td>
          <td nowrap="nowrap" align='center'>
            <?php print $table['size'];?>
          </td>
          <td nowrap="nowrap" align='center'>
            <?php print $table['overhead'];?>
          </td>
          <td nowrap="nowrap" align='center'>
            <?php print $table['auto_inc'];?>
          </td>
          </tr>
        <?php
        $counter++;
        $k = 1 - $k;
        }
        ?>
      </table>
    </div>
    </td>
  </tr>
</table>
    <input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type='hidden' name='param[type]' value='manual'/>
		<input type="hidden" name="controller" value="restore" />
</form>
<script language="javascript">
  function checkAllTables(total, fldName)
  {
     var ck;
     if (document.getElementById('Tables').checked)
     {
       ck = true;
     }
     else
     {
        ck=false;
     }
       for (i=0; i < total; i++) {
         cb = eval( 'document.adminForm.' + fldName + '' + i );
         if (cb)
         {
           cb.checked = ck;
         }
       }

  }
</script>