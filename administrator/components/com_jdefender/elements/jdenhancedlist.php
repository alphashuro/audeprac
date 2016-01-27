<?php defined("_JEXEC") or die("Restricted Access"); /* Mighty Defender FIX */ ?><?php
/**
 * $Id: jdenhancedlist.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined('JPATH_BASE') or die();

/**
 * Renders a list element
 *
 */

class JElementJDEnhancedList extends JElement
{
	function __construct() {
		parent::__construct();
	}
	function fetchElement($name, $value, &$node, $control_name)
	{
		$class 		= ( $node->attributes('class') ? $node->attributes('class') : 'inputbox' );
		$size 		= ( ((int)$node->attributes('size') > 1) ? (int)$node->attributes('size') : 1);
		$multiple 	= ( $node->attributes('multiple') ? true : false );
		$disabled 	= ( $node->attributes('disabled') ? true : false);
		
		$attribs = array(
			'class' => $class,
			'size' => $size
		);
		
		if ($multiple)
			$attribs['multiple'] = 'multiple';
		if ($disabled)
			$attribs['disabled'] = 'disabled';

		if (empty($value))
			$value = array();
		elseif (!is_array($value)) {
			$value = array($value);
		}

		$options = array ();
		$selected = array();
		
		foreach ($node->children() as $option)
		{
			$val	= $option->attributes('value');
			$text	= $option->data();
			
			if (@in_array($val, $value))
				$selected [] = $val;
			elseif ($option->attributes('selected') && (!is_array($value) || !count($value))) {
				$selected [] = $val;
				 
			}
			
			$options[] = JHTML::_('select.option', $val, JText::_($text));
		}

		return JHTML::_('select.genericlist',  $options, ''.$control_name.'['.$name.'][]', $attribs, 'value', 'text', $selected, $control_name.$name);
	}
}