<?php
/**
 * $Id: jddirectory.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined('_JEXEC') or die('Restricted Access');

/**
 * entodo: Complete the element to add ability to choose folders via ajax
 * @author Nurlan
 *
 */
class JElementJDDirectory extends JElement
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'Article';

	function fetchElement($name, $value, &$node, $control_name)
	{
		$document = & JFactory::getDocument();
		$document->addScriptDeclaration(
			"window.addEvent('domready', function() {
				$$('a.jd_directory').each(function( e ) {
					e.addEvent('click', function() {
						e.setHTML();
					});
				});
			}
		");
		
		$html = '<div class="button2-left"><div class="blank">'.
			'<a class="jd_directory" title="'.JText::_('Select an Article').'" href="javascript:void(0);">'.JText::_('Directories').'</a>'.
			'</div></div>'."\n";
		$html .= '<div style="clear:both">';
		
		$rows = $node->attributes('rows');
		$cols = $node->attributes('cols');
		$class = ( $node->attributes('class') ? 'class="'.$node->attributes('class').'"' : 'class="text_area"' );
		
		// convert <br /> tags so they are not visible when editing
		$value = str_replace('<br />', "\n", $value);

		return $html.'<textarea name="'.$control_name.'['.$name.']" cols="'.$cols.'" rows="'.$rows.'" '.$class.' id="'.$control_name.$name.'" >'.$value.'</textarea>';
	}
}