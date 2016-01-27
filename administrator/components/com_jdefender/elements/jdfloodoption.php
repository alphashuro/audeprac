<?php
/**
 * $Id: jdfloodoption.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined ('_JEXEC') or die('Restricted Access');

class JElementJDFloodOption extends JElement
{
	function fetchElement($name, $value, &$node, $control_name)
	{
		$timeMeasure = @$node->attributes('time_measure', 'seconds');
		
		$defaultQueries = @$node->attributes('default_queries', '');
		$defaultTime	= @$node->attributes('default_time', 	'');
		
		
		if (!is_array($value)) {
			$value = array();
			$value[ 0 ] = $defaultQueries;
			$value[ 1 ] = $defaultTime;
		}
		
		$enabled = 0;
		if (count($value) == 4) {
			$enabled = $value[0];
			array_shift($value);
		} 
		elseif (count($value) == 3)
			$enabled = 1;
		
		$html = '';
		
		if ($node->attributes('selectable', 0))
			$html = JHTMLSelect::booleanlist($control_name.'['.$name.'][]', 'class="inputbox"', $enabled);
		else
			$html = '<div style="float:left; width:87px; clear: both">&nbsp;</div>';
		
		$baseName = $control_name.'['.$name.']';
			
		$html .= '<input id="'.$baseName.'0" style="width: 50px" type="text" name="'.$baseName.'[]" value="'.@$value[ 0 ].'" class="inputbox" /> '.JText::_('or more queries in').' ';
		$html .= '<input id="'.$baseName.'1" style="width: 50px" type="text" name="'.$baseName.'[]" value="'.@$value[ 1 ].'" class="inputbox" /> '.JString::ucfirst(JText::_($timeMeasure)).' ';
		$html .= '<input id="'.$baseName.'2" type="hidden" name="'.$baseName.'[]" value="'.$timeMeasure.'" />';
		
		if ($timeMeasure) 
		{
			ob_start();
			?>
			window.addEvent('domready', function() {
				var c = $('<?php echo $baseName?>[]0');
				if ( c ) {
					c.addEvent('click', function() {
						$('<?php echo $baseName?>0').setProperty('disabled', true);
						$('<?php echo $baseName?>1').setProperty('disabled', true);
						$('<?php echo $baseName?>2').setProperty('disabled', true);
					});
					
					if (c.getProperty('checked')) {
						$('<?php echo $baseName?>0').setProperty('disabled', true);
						$('<?php echo $baseName?>1').setProperty('disabled', true);
						$('<?php echo $baseName?>2').setProperty('disabled', true);
					}
				}
				
				c = $('<?php echo $baseName?>[]1');
				if ( c ) {
					c.addEvent('click', function() {
						$('<?php echo $baseName?>0').removeProperty('disabled');
						$('<?php echo $baseName?>1').removeProperty('disabled');
						$('<?php echo $baseName?>2').removeProperty('disabled');
					});
				}
			});
			<?php
			$document = & JFactory::getDocument();
			$document->addScriptDeclaration(ob_get_clean());
		}

		return '<div style="white-space:nowrap;">'.$html.'</div>';
	}
}


?>