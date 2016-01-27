<?php
/**
 * $Id: spam.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined ( '_JEXEC' ) or die ( 'Access Restricted' );

/**
 * (C) The following code is taken from Michiel Bijland's (http://michiel.bijland.net) HttpBL plugin
 *
 */

class JD_Spam_Helper extends JObject 
{	
	function getSpammer($ip) 
	{
		require_once JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_jdefender'.DS.'helpers'.DS.'vars.php';
		
		$long = ip2long ( trim($ip) );
		
		if ($long == - 1 || $long === false) {
			return false;
		}
		
		// Get Plugin info
		$params = & JD_Spam_Helper::getParams();
		
		// get API key
		$key = $params->get ( 'spam_key' );
		
		// without key this plugins has no use.
		if (empty ( $key )) {
			return false;
		}
		
		$response = JD_Vars_Helper::getVar($ip, 'spam_cache');

		if (! $response) {
			// Query
			$ip = implode ( '.', array_reverse ( explode ( '.', $ip ) ) );
			$query = $key . '.' . $ip . '.dnsbl.httpbl.org';
			$response = gethostbyname ( $query );
			
			// Did the lookup fail, if so either not listed or error
			if ($query == $response) {
				// rewrite responce so key isn't written to cache file and save precious space.
				$response = '0.0.0.0';
			}
			
			// store data
			JD_Vars_Helper::setVar($ip, 'spam_cache', $response);
		}
		
		// explode responce
		$response = explode ( '.', $response );
		
		// If the response is positive,
		if ($response [0] == 127) {
			
			// Get thresholds
			$age = $params->get ( 'spam_age', 30 );
			$threat = $params->get ( 'spam_threat', 25 );
			
			$whoToBlock = $params->get('spam_seek', array());
			settype($whoToBlock, 'array');
			
			// Who to block
			$seek_s = array_search(1, $whoToBlock) !== false ? 1 : 0;
			$seek_h = array_search(2, $whoToBlock) !== false ? 2 : 0;
			$seek_c = array_search(4, $whoToBlock) !== false ? 4 : 0;
			
			$seek = $seek_s | $seek_h | $seek_c;
			
			if ($response [1] < $age && $response [2] > $threat && ($response [3] & $seek > 0)) {
				$spammer = new stdClass;
				
				$spammer->suspicious 		= $response[3] & 1;
				$spammer->harvester 		= $response[3] & 2;
				$spammer->comment_spammer 	= $response[3] & 4;
				$spammer->age 				= $response[1];
				$spammer->threat 			= $response[2];
				
				$spammer->attacker			= $response[3];
				
				return $spammer;
			}
		}
		
		return false;
	}
	
	function codeToString($code) {
		$result = array();
		
		if ($code & 1)
			$result [] = JText::_('Suspicious');
		if ($code & 2)
			$result [] = JText::_('Harvester');
		if ($code & 4)
			$result [] = JText::_('Comment spammer');
		
		return count($result) ? implode(', ', $result) : false;
	}
	
	function threatToPercentage($threat) {
		$threat = (int)$threat;
		
		$threat = min($threat, 255);
		$threat = max($threat, 0);
		
		$result = 100 * $threat / 255;
		
		return (int)round($result);
	}
	
	function & getParams() {
		static $params = null;
		
		if (empty($params))
		{
			$model = & JModel::getInstance('Configuration', 'JDefenderModel');
			$params = new JParameter($model->getIni());
		}
		
		return $params;
	}
}