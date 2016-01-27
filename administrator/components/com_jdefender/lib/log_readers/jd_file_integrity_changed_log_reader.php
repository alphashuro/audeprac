<?php
/**
 * $Id: jd_file_integrity_changed_log_reader.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined ('_JEXEC') or die('Restricted Access');

/**
 * Class For reading and displaying various types of logs.
 * @author Nurlan
 *
 */
class JD_File_Integrity_Changed_Log_Reader extends JD_Log_Reader
{
	function __construct() {
		parent::__construct();
	}
	
	/**
	 * Returns array with tabular data. 
	 */
	function getTables() {
		$actual 	= $this->read($this->_logRecord);
		$current 	= $this->readCurrentState($this->_filesystemState);
			
		$keys = array_keys($actual);
		
		$data = array();
		foreach ($keys as $k) {
			$row = array();
			$row [] = '<b>'.$this->_decorateWord($k).'</b>';
			
			if ($k == 'size') {
				if (!empty($actual[$k]))
					$actual[$k] = JD_Log_Helper::formatSize($actual[$k]);
				if (!empty($current[$k]))
					$current[$k] = JD_Log_Helper::formatSize($current[$k]);
			}
			elseif (in_array($k, array('ctime', 'mtime'))) {
				if (!empty($actual[$k]))
					$actual[$k] = JD_Log_Helper::formatDate($actual[$k]);
				if (!empty($current[$k]))
					$current[$k] = JD_Log_Helper::formatDate($current[$k]);
			}
			
			if (empty($actual[$k]))
				$row [] = '&nbsp;';
			else 
				$row [] = $actual[$k];
			
			if (empty($current[$k])) {
				if ($current)
					$row [] = '&nbsp;';
			}
			else
				$row [] = $current[$k];
			
			$data [] = $row;
		}
		
		if (count($data)) {
			$toAdd = array('&nbsp;', '<b>'.JText::_('Current').'</b>');
			if (!empty($this->_filesystemState))
				$toAdd [] = '<b>'.JText::_('Last Scan').'</b>';
		
			array_unshift($data, $toAdd);
		}
		
		unset($this->_logRecord->url);
//		unset($this->_logRecord->status);
		
		return array($data);
	}
	
	/**
	 * Returns assoc array of values to display
	 * (non-PHPdoc)
	 * @see components/com_jdefender/lib/log_readers/JD_Log_Reader#read($logRecord)
	 */
	function read($logRecord) {
		if (empty($logRecord) || empty($logRecord->post))	
			return null;

		$data = array();
		
		if (strpos($logRecord->url, 'http://') !== false || strpos($logRecord->url, 'https://') !== false)
			$data['URL'] = $logRecord->url;
		else
			$data['File/Directory'] = $logRecord->url;
		
		if (!is_array($logRecord->post)) {
			$d = @unserialize($logRecord->post);
		}
		else
			$d = $logRecord->post;
		
		if (is_array($d))
			$data = array_merge($data, $d);

		
		if (!is_array($data))
			return null;
		
		
		return $this->_parseArray($data);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see components/com_jdefender/lib/log_readers/JD_Log_Reader#readCurrentState($logRecord)
	 */
	function readCurrentState($state) {
		if (empty($state))
			return null;
		
		return $this->_parseArray(JArrayHelper::fromObject($state));
	}
	
	function _parseArray($data) {
		$uid 		= empty($data['uid']) ? null : $data['uid'];
		$gid 		= empty($data['gid']) ? null : $data['gid'];
		$permission = empty($data['permission']) ? null : $data['permission'];
		
		$result = $data; 
	
		if (function_exists('posix_getpwuid')) {
			if (!is_null($uid)) {
				$usr 		= posix_getpwuid($uid);
				$username 	= $usr['name'];
				$result['user'] = $username;
				unset($result['uid']);
			}
			
			if (!is_null($gid)) {
				$grp   		= posix_getgrgid($gid);
				$groupname 	= $grp['name'];
				$result['group'] = $groupname;
				unset($result['gid']);
			}
		}
		
		if (!is_null($permission)) {
			$result['permissions'] = sprintf('%o', (int)$data['permission']);
		}		
		
		return $result;
	}
	
	function _decorateWord($word) {
		switch ($word) {
			case 'hash_md':
				return JText::_('MD5 Hash');
			case 'ctime':
				return JText::_('Created time');
			case 'mtime':
				return JText::_('Modified');
			case 'size':
				return JText::_('Size');
			default:
				return JString::ucwords(JText::_(str_replace('_', ' ', $word)));
		}
	}
}