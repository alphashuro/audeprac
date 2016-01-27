<?php
/**
 * $Id: log.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined ('_JEXEC') or die('Access Restricted');

class JD_Log_Helper extends JObject
{
	function formatDate($date) {
		$d = & JFactory::getDate($date);
		
		return $d->toFormat(JD_Vars_Helper::getVar('other_date_format', 'configuration', '%A, %d %B %Y'));
	}
	
	function formatSize($size) {
	    $kb = 1024;
	    $mgb = $kb * 1024;
	    $gb  = $mgb * 1024;
	    $trb = $gb * 1024;
	    
	    if($size > $trb)
	    {
	        return number_format($size / $trb, 3)." Tb";
	    }
	    elseif($size > $gb)
	    {
	        return number_format($size / $gb, 3)." Gb";
	    }
	    elseif($size > $mgb)
	    {
	        return number_format($size / $mgb, 3)." Mb";
	    }
	    elseif($size > $kb)
	    {
	        return number_format($size / $kb, 3)." Kb";
	    }
	    else
	    {
	        return $size." B";
	    }
	}
	/**
	 * Returns readable log type title and description
	 * @param unknown_type $type log type
	 */
	function readableLogType($type) 
	{
		$result = new stdClass;
		switch ($type) {
			case 'file_integrity_new':
				$result->title = 'file_integrity - new files';
				$result->description = JText::_('New files');
				break;
			
			case 'file_integrity_changed':
				$result->title = 'file_integrity - modified files';
				$result->description = JText::_('Modified files');
				break;
			
			case 'file_integrity_php_bad_functions':
				$result->title = 'file_integrity - PHP Bad Functions';
				$result->description = JText::_('The PHP files containing bad functions, that are not recommended to use');
				break;
			
			case 'file_integrity_php_jexec':
				$result->title = 'file_integrity - Insecure PHP File';
				$result->description = JText::_('Missing _JEXEC check in PHP files');
				break;
			
			case 'missing_file':
				$result->title = 'File Integrity - Missing Files';
				$result->description = JText::_('Missing files');
				break;
				
			case 'folder_safety_index':
				$result->title = 'Insecure folder';
				$result->description = JText::_('No index.html in Folder');
				break;
				
			case 'flood':
				$result->description = JText::_('Flood attempts (DoS)');
				break;
				
			case 'php_injection':
				$result->description = JText::_('PHP injection attacks');
				$result->title = 'PHP Injection';
				break;
			
			case 'sql_injection':
				$result->description = JText::_('SQL injection attacks');
				$result->title = 'SQL Injection';
				break;
			case 'spam':
				$result->title = 'Spammers';
				$result->description = JText::_('SPAM attacks');
				break;
			case 'permission':
				$result->title = 'Filesystem - Permissions';
				$result->description = JText::_('Permission problems');
				break;
			case 'options':
				$result->title = 'System Options';
				$result->description = JText::_('System stuff');
				break;
			case 'blocked_users_ips':
				$result->title = 'Blocked Users And IPs';
				$result->description = JText::_('Access from blocked IPs, usernames and referers');
				break;
			default:
				return false;
				break;
		}
		
		if (empty($result->title))
			$result->title = str_replace('_', ' ', $type);
		else
			$result->title = str_replace('_', ' ', $result->title);
			
		$result->title = JText::_(JString::ucwords($result->title));
		
		return $result;
	}
	
	function beautifyString($str, $translate = false) {
		$result = str_replace('_', ' ', $str);
		$result = JString::ucwords($result);
			
		return $translate ? JText::_($result) : $result;
	}

	
	/**
	 * Write a log entry with Get/Post params
	 * @param $type
	 * @param $status
	 * @param $issue
	 * @param $opts
	 */
	function log($type, $status, $issue = '', $opts = false)
	{
		$db 	= &JFactory::getDBO();
		$user 	= &JFactory::getUser();
		
		$user_id 	= $user->get('id');
		$UserIp 	= isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : getenv("REMOTE_ADDR");
		$ref 		= isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : getenv("HTTP_REFERER");;
		
		$uri = & JURI::getInstance();
		
		$get 		= $uri->toString();
		$cookies	= serialize(JRequest::get('cookie', 2));
		$userAgent	= isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : getenv('HTTP_USER_AGENT');
		
		$post = JRequest::get('post', 2);
		if ($opts)
			$post['__mighty_defender_info'] = $opts;
		$post = serialize($post);
		
		$extension	= '';
		if (JRequest::getString('option'))
			$extension	= 'component::::'.JRequest::getString('option');
		
		$q = "INSERT INTO #__jdefender_log ".
			"(`ip`, `ctime`, `type`, `user_id`, `url`, `post`, `cook`, `referer`, `status`, `issue`, `user_agent`, `extension`, `total`) " .
		"VALUES ".
			"(".$db->Quote($UserIp).", NOW(),".$db->Quote($type).",'{$user_id}',".$db->Quote($get).",".$db->Quote($post).", " .
			$db->Quote($cookies).", ".$db->Quote($ref).", ".$db->Quote($status).", ".$db->Quote($issue).", ".$db->Quote($userAgent).", ".
			$db->Quote($extension).", 1)";
		
		$db->setQuery($q);
		$db->query();
	}
	
	function logSmartly($type, $status, $issue = '', $opts = false) {
		$db 	= &JFactory::getDBO();
		$user 	= &JFactory::getUser();
		
		$user_id 	= $user->get('id');
		$UserIp 	= isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : getenv("REMOTE_ADDR");
		$ref 		= isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : getenv("HTTP_REFERER");
		
		$post = JRequest::get('post', 2);
		if ($opts)
			$post['__mighty_defender_info'] = $opts;
		$post = serialize($post);
		
		$uri = & JURI::getInstance();
		
		$q = "SELECT `id` FROM #__jdefender_log WHERE `ip` = ".$db->Quote($UserIp)." AND `url` = ".$db->Quote($uri->toString())
			.' AND `type` = '.$db->Quote($type).' AND status = '.$db->Quote($status)
			.' AND `post` = '.$db->Quote($post)
			." AND `issue` = ".$db->Quote($issue).' AND `ctime` + INTERVAL 1 HOUR > NOW()';
		
		$db->setQuery( $q );
		
		$id = $db->loadResult();
		
		if ( $id ) {
			$db->setQuery("UPDATE #__jdefender_log SET `ctime` = NOW(), `total` = `total` + 1 WHERE `id` = ".(int)$id);
			$db->query();
		}
		else {
			JD_Log_Helper::log($type, $status, $issue, $opts);
		}
	}
}