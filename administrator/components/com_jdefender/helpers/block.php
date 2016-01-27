<?php
/**
 * $Id: block.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined ('_JEXEC') or die ("Access Restricted");


class JD_Block_Helper
{	
	function block($type, $value, $reason) {
		$row = & JTable::getInstance('Block', 'Table');
		
		if ($row->loadByTypeAndValue($type, $value))
		{
			if ( ! $row->published) {
				$row->published = 1;
				return $row->store();
			}
		}
			
		$row->type 	= $type;
		$row->value = $value;
		$row->reason = $reason;
		
		return $row->store();
	}
	
	function unblock($type, $value) {
		$db = & JFactory::getDBO();
		
		$db->setQuery('DELETE FROM #__jdefender_block_list WHERE `type` = '.$db->Quote($type).' AND `value` = '.$db->Quote($value));
		
		return $db->query();
	}
	
	function blockPHP($file) {
		$f = new JD_File($file);
		$blockString = '<?php /* ATTENTION!! DEFENDER GENERATED CODE. DO NOT DELETE!! */ return; ?>';
			
		// Try to read file
		$contents = $f->read();
		
		if ($contents === false) {
			return JText::_('Cannot block file').': '.$f->getError();
		}
		
		// Try to write
		if ( ! $f->write($blockString.$contents) ) {
			return (JText::_('Cannot block file').': '. $f->getError());
		}
		
		return true;
	}
	
	function unblockPHP($file) {
		$f = new JD_File($file);
		$blockString = '<?php /* ATTENTION!! DEFENDER GENERATED CODE. DO NOT DELETE!! */ return; ?>';
			
		// Try to read file
		$contents = $f->read();
		
		if ($contents === false) {
			return JText::_('Cannot unblock file').': '.$f->getError();
		}
		
		$toWrite = str_replace($blockString, '', $contents);
		
		// Try to write
		if ( ! $f->write($toWrite) ) {
			return JText::_('Cannot unblock file').': '. $f->getError();
		}
		
		return true;
	}
	
	/**
	 * 
	 * @param $userId
	 * @param $refresh boolean Refresh the local cache
	 */
	function isUserBlocked($userId, $refresh = false) {
		static $userState = array();
		
		$userId = (int)$userId;
		
		if (!isset($userState[ $userId ]) || $refresh)
		{	
			$db = & JFactory::getDBO();
			$db->setQuery('SELECT COUNT(*) FROM #__jdefender_block_list WHERE (`type` = "user" OR `type` = "login") AND `value` = '.$userId.' AND published = 1');
			$userState[ $userId ] = !!$db->loadResult();
		}
		
		return $userState[ $userId ]; 
	}
	
	/**
	 * Check whether IP is blocked
	 * @param $ip
	 * @param $checkRangesOnly
	 * @param $refresh
	 */
	function isIPBlocked($ip, $checkRangesOnly = false, $refresh = false) {
		static $ipStatus = array();
		
		$ip = trim($ip);
		
		if (!isset($ipStatus[$ip][(int)!!$checkRangesOnly]) || $refresh)
		{
			$db = & JFactory::getDBO();
			
			$result = false;
			
			if ( ! $checkRangesOnly)
			{
				$db->setQuery('SELECT COUNT(*) FROM #__jdefender_block_list WHERE `type` = "ip" AND `value` = '.$db->Quote($ip).' AND published = 1');
				$result = $db->loadResult();
				
				if ($result > 0) {
					$ipStatus[$ip][(int)!!$checkRangesOnly] = true;
					return true;
				}
			}
			
			
			$ip2long = ip2long($ip);
			
			$IPRanges = & JD_Block_Helper::getRangedIpBlocks();
			
			
			if ($IPRanges) {
				foreach ($IPRanges as $entry) {
					$range = explode('-', $entry);
					if (count($range) != 2)
						continue;
					
					$lower = ip2long(trim($range[0]));
					$upper = ip2long(trim($range[1]));
					
					if ($lower == -1 || $lower === false || $upper == -1 || $upper == false) {
						continue;
					}
					
					if ( ($lower <= $ip2long && $ip2long <= $upper) || ($lower >= $ip2long && $ip2long >= $upper) ) {
						$ipStatus[$ip][(int)!!$checkRangesOnly] = true;
						return true;
					}
				}
			}
			
			
			$ipStatus[$ip][(int)!!$checkRangesOnly] = false;
		}
		
		return $ipStatus[$ip][(int)!!$checkRangesOnly];
	}
	
	function & getRangedIpBlocks() {
		static $IPRanges = 0;
		
		if ($IPRanges == 0)
		{
			// Search for blocked IP ranges.
			$db = & JFactory::getDBO();
			$q = 'SELECT `value` AS `range` FROM #__jdefender_block_list WHERE type = "ip" AND published = 1 AND LOCATE("-", `value`)';
			$db->setQuery( $q );
			
			$IPRanges = $db->loadResultArray(0);
		}
		
		return $IPRanges;
	} 
}