<?php
/**
 * $Id: add.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined('_JEXEC') or die('Restricted Access');

JHTML::_('behavior.tooltip');

$i = 0;
$c = 0;
?>
<style type="text/css">
.jdef_red
{
	color:red;
	padding-left: 16px;
}
#table a:hover {
	text-decoration: none;
}
.jdef_green
{
	color:green;
}
</style>
<form action = 'index.php' method='post' name = 'adminForm'>
<!--<fieldset>
<legend><?php echo JText::_("Add Block Criteria");?></legend>
--><table class="admintable" width="600">
	<thead>
		<tr>
			<th><?php echo JText::_("Type"); ?></th>
			<th>
				<span class="hasTip" title="<?php echo JText::_('Enter value to block'), '::', JText::_('You can specify ip address range in this format:<br /> <b>abc.def.ghi.abc - abc.def.mno.xyz</b>'); ?>">
					<?php echo JText::_("Value"); ?>
				</span>
			</th>
			<th><?php echo JText::_("Reason"); ?></th>
			<th></th>
		</tr>
	</thead>
	<tbody id="table">
		<?php
			$options = array();
			$options[] = JHTML::_('select.option', JText::_('IP Address (or range)'),'ip');
			$options[] = JHTML::_('select.option', JText::_('Referer'),'referer' );
			$options[] = JHTML::_('select.option', JText::_('User ID'), 'user' ); 
		?>
		<?php foreach ($this->vars as $k => $row) : ?>
		<tr class="row<?php echo $i; $i = 1 - $i; ?>" id="row<?php echo $k; ?>">
			<td class="value">
				<?php
					echo JHTML::_('select.genericlist', $options, 'var['.$k.'][type]', 'class="inputbox"', 'text', 'value', @$row['type']);
					echo '<input type="hidden" name="var[',  $k, '][id]" value="', @$row['id'], '" />';
				?>
			</td>
			<td class="value">
				<input type="text" name="var[<?php echo $k; ?>][value]" size="40" value="<?php echo @$row['value']; ?>" />
			</td>
			<td class="value">
				<input type="text" name="var[<?php echo $k; ?>][reason]" size="40" value="<?php echo isset($row['reason']) ? $row['reason'] : JText::_('Not specified'); ?>" />
			</td>
			<td nowrap="nowrap">
				<?php if ($c && empty($row['id'])) : ?>
				<a href ="javascript:jsaclRemove('<?php echo $k; ?>')">
					<span class="jdef_red icon-16-ignore">
						<?php echo JText::_("Remove")?>
					</span>
				</a>
				<?php else: ?>
					<a href="javascript:jsaclAddAttr()"><span style="padding-left: 18px;" class="icon-16-new jdef_green"><?php echo JText::_("More..."); ?></span></a>
				<?php endif; ?>
			</td>
		</tr>
		<?php $c++;	?>
		<?php endforeach; ?>
	</tbody>
</table>
<!--</fieldset>-->



<input type='hidden' id='urlcounter' name = 'urlcounter' value='<?php echo 2;?>'>
<input type="hidden" name="option" value="com_jdefender" />
<input type="hidden" name="controller" value="block" />
<input type="hidden" name="task" value="" />
</form>
<script language="javascript">

var counter = 2;

function jsaclAddAttr()
{
	var table = document.getElementById('table');
	var tr = document.createElement("TR");

	var td1 = document.createElement("TD");
	var td2 = document.createElement("TD");
	var td3 = document.createElement("TD");
	var td4 = document.createElement("TD");

	var urlcounter = document.getElementById('urlcounter');

	td1.innerHTML = "<select name='var["+counter+"][type]'>"+"<option value='ip'><?php echo JText::_('IP Address (or range)', true); ?></option>"+"<option value='referer'><?php echo JText::_("Referer"); ?></option>"+"<option value='user'><?php echo JText::_("User ID"); ?></option>"+"</select>";
	td1.setAttribute('class', 'value');
	
	td2.innerHTML = '<input type="text" name="var['+counter+'][value]" size="40" />';
	td2.setAttribute('class', 'value');
	
	td3.innerHTML = '<input type="text" name="var['+counter+'][reason]" size="40" value="<?php echo JText::_('Not specified', true); ?>" />';
	td3.setAttribute('class', 'value');
	
	td4.innerHTML = "<a href=\"javascript:jsaclRemove("+counter+")\"><span class=\"jdef_red icon-16-ignore\"><?php echo JText::_("Remove", true)?></span></a>";

	var input = document.createElement('INPUT');
	input.type = 'hidden';
	input.name = 'var[' + counter + '][id]';
	input.value = '';

	td1.appendChild(input);
	
	tr.setAttribute('id', 'row'+counter);
	tr.className = 'row' + ((counter  + 1) % 2);
	
	tr.appendChild(td1);
	tr.appendChild(td2);
	tr.appendChild(td3);
	tr.appendChild(td4);

    table.appendChild(tr);

    $(tr).setStyle('opacity', 0);

    tr.effect('opacity', {duration: 500}).start(0, 1);

    urlcounter.value = counter + 1;

    counter ++;
}



function jsaclRemove(num)
{ 
	if (num == 1)
		return;

	$('row' + num).effect('opacity', {duration: 400}).start(1, 0).chain(function() {
		$('row' + num).remove();
	});
}
</script>
