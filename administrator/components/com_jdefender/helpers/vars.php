<?php
/**
 * $Id: vars.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined ('_JEXEC') or die('Access Restricted');


class JD_Vars_Helper extends JObject
{
	function getGroup($type, $default = null) {
		$vars = & JD_Vars_Helper::_getVars();
		
		if (isset($vars[$type]))
			return $vars[$type];
		
		return $default;
	}
	
	function getVar($name, $type, $default = null) {
		$vars = & JD_Vars_Helper::_getVars();
		
		if (isset($vars[$type][$name]))
			return $vars[$type][$name];
		
		return $default;
	}
	
	function setVar($name, $type, $value) {
		$oldValue = JD_Vars_Helper::getVar($name, $type);
		
		if ($oldValue === $value)
			return $oldValue;
		
		$db = & JFactory::getDBO();
		
		if (!is_null($oldValue))
		{
			$db->setQuery('DELETE FROM #__jdefender_vars WHERE name = '.$db->Quote($name).' AND `type` = '.$db->Quote($type));
			$db->query();
		}
		
		$db->setQuery('INSERT INTO #__jdefender_vars (`name`, `type`, `value`) VALUES ('.$db->Quote($name).', '.$db->Quote($type).', '.$db->Quote(@serialize($value)).')');
		$db->query();
		
		$vars = & JD_Vars_Helper::_getVars();
		$vars[$type][$name] = $value;
		
		return $oldValue;
	}
	
	function purgeVars($type, $minutes = 60) {
		$minutes = (int)$minutes;
		
		$db = & JFactory::getDBO();
		
		$q = 'DELETE FROM #__jdefender_vars WHERE `type` = '.$db->Quote($type);
		
		if ($minutes)
			$q .= ' AND `ctime` + INTERVAL '.$minutes.' MINUTES < NOW()';
		
		$db->setQuery( $q );
		$db->query();
		
		$vars = & JD_Vars_Helper::_getVars();
		$vars[$type] = array();
	}
	
	/**
	 * @access private
	 */
	function & _getVars() {
		static $vars = array();
		
		if (empty($vars)) {
			$db = & JFactory::getDBO();
			$db->setQuery('SELECT * FROM #__jdefender_vars ORDER BY `type`, `name`');
			
			$result = $db->loadObjectList();
			if (empty($result))
				$result = array();
			
			foreach ($result as $row) {
				$vars[$row->type][$row->name] = unserialize($row->value);
			}
		}
		
		return $vars;
	}
}