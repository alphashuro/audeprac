<?php
/**
 * $Id: exceptionadder.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined('_JEXEC') or die('Access Restricted');


class JElementExceptionAdder extends JElement
{
	function fetchElement($name, $value, & $node, $control_name) 
	{
		$target = $node->attributes("target");
		
		$link = JHTML::link(
			'javascript:void(0);', 
			'<input type="button" value="'.htmlspecialchars(JText::_('Add Exception'), ENT_QUOTES, 'UTF-8').'" />',
			array(
				'class' => 'button',
				'id' => $control_name.$name.'id'
			)
		);
		
		$components = & $this->_getComponents();
		$options = array();
		
		$options [] = JHTML::_('select.option', '*', 'All components');
		foreach ($components as $c)
			if ($c->option)
				$options [] = JHTML::_('select.option', $c->option, $c->option);
		
		$select = JHTML::_('select.genericlist', $options, $control_name.$name.'cmp');
		
		ob_start();
		?>
		<table cellpadding="0" cellspacing="0">
			<tr>
				<td><?php echo JText::_('Component'); ?>:</td>
				<td><?php echo $select; ?></td>
			</tr>
			<tr>
				<td><?php echo JText::_('Variable'); ?>:</td>
				<td><input type="text" class="inputbox" id="<?php echo $control_name.$name.'var'; ?>" /></td>
			</tr>
		</table>
		<?php
		$result = ob_get_clean();
		
		JHTML::_('behavior.mootools');
		
		ob_start();
		?>
		window.addEvent('domready', function() {
			$('<?php echo $control_name.$name.'id'; ?>').addEvent('click', function() {
				var v = $('<?php echo $control_name.$name.'var'; ?>');
				if (!v.value) {
					alert('<?php echo JText::_('Please enter variable name', true)?>');
					return;
				}
				
				var c = $('<?php echo $control_name.$name.'cmp'; ?>');
				
				var target = $('<?php echo $target; ?>');
				
				if (target) {
					if ( target.value.split("\n").indexOf(c.value + '::' + v.value) < 0 ) {
						if (target.value)
							target.value += "\n";
							
						target.value += c.value + '::' + v.value;
					}
					else
						alert('<?php echo JText::_('The rule is already added', true); ?>');
				}
			});
		});
		<?php 
		$doc = & JFactory::getDocument();
		$doc->addScriptDeclaration(ob_get_clean());
		

		return $result. $link;
	}
	
	
	function _getComponents() {
		static $components = 0;
		
		if (empty($components))
		{
			$db = & JFactory::getDBO();
			$db->setQuery('SELECT `option` FROM #__components GROUP BY `option` ORDER BY `option`');
			
			$components = $db->loadObjectList();
		}
		
		return $components;
	}
}