<?php
/**
 * $Id: jd_file.php 7311 2011-08-19 11:18:02Z shitz $
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
 * Class for writing and reading to/from files regardless to their permissions
 * @author nurlan
 *
 */
class JD_File extends JObject
{
	/**
	 * File name
	 * @var string
	 */
	var $_filename;
	/**
	 * Array that holds file info, retreived by "stat" function
	 * @var unknown_type
	 */
	var $_fileInfo;
	
	/**
	 * 
	 * @var boolean
	 */
	var $_fileExists;
	
	function __construct($filename) {
		parent::__construct();
		
		jimport ('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		
		$this->_filename = $filename;
	}
	
	/**
	 * Returns file information using 'stat' function
	 * @return mixed
	 */
	function getFileInfo() {
		if (empty($this->_fileInfo)) {
			@clearstatcache();
			
			$this->_fileInfo = @stat($this->_filename);
			if (empty($this->_fileInfo)) {
				$this->setError(JText::_('Cannot execute stat for file').': '.$this->_filename);
			} 
		}
		return $this->_fileInfo;
	}
	
	/**
	 * entodo: add FTP write
	 * Write data to file, trying to change permissions if needed
	 * @param $data
	 * @return unknown_type
	 */
	function write($data = '')
	{
		$dir = dirname($this->_filename);
		
		$dirCreated = false;
		// Create the destination directory if it doesn't exist
		if (!file_exists($dir)) {
			if (!JFolder::create($dir)) 
			{
				$this->setError(JText::_('Cannot create directory').': '.$dir);
				return false;
			}
			
			$dirCreated = true;
		}
	
		@clearstatcache();
		$fileExists		= $this->exists();
		
		$fileChmodded 	= false;
		$filePerms		= 0644;
		$dirPerms 		= fileperms($dir) & 0777;
		$chmodder 		= $this->_getChmodderInfo();
		
		if ($fileExists) {
			
			if (is_writable($this->_filename)) {
				return @file_put_contents($this->_filename, $data);
			}
			
			$fileInfo = $this->getFileInfo();
			if (!$fileInfo) {
				return false;
			}
			
			$filePerms = $fileInfo['mode'] & 0777;
			$ownerInfo = $this->_getFileOwnerInfo($fileInfo);
			
			// Defined the mask
			$mask = false;
			if ($ownerInfo['user'] == $chmodder['user'])
				$mask = 0200;
			elseif ($ownerInfo['user'] != $chmodder['user'] && $ownerInfo['group'] == $chmodder['group'])
				$mask = 0020;
			else 
				$mask = 0002;
			
			if (!($filePerms & $mask))
			{
				if (false === $this->chmod($filePerms | $mask))
					return false;
					
				$fileChmodded = true;
			}
		}
		
		$failed = false;
		
		$dirPermsChanged = false;
		$theDir = new JD_File($dir);
		
		if ( !$fileExists) {
			if (false === JFile::write($this->_filename, $data)) {
				if (false === @file_put_contents($this->_filename, $data)) {
					if (false === $theDir->chmod($dirPerms | 0222))	{
						$this->setError($theDir->getError());
						return false;
					}
				}
			}
		}

		$result = JFile::write($this->_filename, $data);
		
		if ($fileChmodded)
			$this->chmod($filePerms);
		
		if (!$dirCreated)
			$theDir->chmod($dirPerms);

		return $result;
	}
	
	/**
	 * Read file
	 * @return mixed
	 */
	function read() {
		if (!$this->exists()) {
			$this->setError(JText::_('No such file exists').': '.$this->_filename);
			return false;
		}
		
		clearstatcache();
		if (is_readable($this->_filename))
			return JFile::read($this->_filename);

		// If the file is not readable
		$fileInfo = $this->getFileInfo();
		if (!$fileInfo)
			return false;
		$permission = $fileInfo['mode'] & 0777;
		
		if (!$this->canChmod()) {
			$this->setError(JText::_('Cannot read file. Operation not permitted. File').': '.$this->_filename);
			return false;
		}
		
		if (!$this->chmod($permission | 0444))
		{
			$this->setError(JText::_('Cannot read file. Operation not permitted. File').': '.$this->_filename);
			return false;
		}
		
		$contents = JFile::read($this->_filename);
		
		$this->chmod($permission);
		
		return $contents;
	}
	
	function chmod($permission) 
	{	
		$permission = ((int)$permission) & 0777;
		
		// First, try simple chmod
		if (@chmod($this->_filename, $permission))
		{
			return true;
		}
		else {
			// try FTP
			$ftp = & JD_Ftp::getFtpHandle();
			
			// FTP Mode enabled
			if ($ftp) 
			{
				$file = JD_Ftp::translatePath($this->_filename);
				
				// ignore ftp errors messages
				JD_Error::unsetErrorHandlers();
				
				$result = $ftp->chmod($file, $permission);
				
				JD_Error::putErrorHandlersBack();
				
				if (false === $result) {
					$this->setError($this->_getPersmissionError($permission));
					return false;
				}
				return true;
			}
			else {
				// Return the error
				$error = $this->_getPersmissionError($permission);
				$this->setError($error);
				return false;
			}
		}
	}
	
	function _getPersmissionError($permission)
	{
		$dir 		= dirname($this->_filename);
		$dirPerms 	= fileperms($dir);
		
		$fileExists = file_exists($this->_filename);
		
		if ($fileExists)
		{
			$error = JText::_('Cannot change permissions, Operation not permitted').'<br />'.
				JText::_('File').': '.$this->_filename.'. '.JText::_('Trying to set permission').': '.decoct($permission);
		}
		else
		{
			$error = JText::_('Cannot change permissions, Operation not permitted').'<br />'.
				JText::_('Directory').': '.$dir.'. '.JText::_('Trying to set permission').': '.decoct($permission);
		}
		
		// Append user and group info
		$info 		= $fileExists ? $this->getFileInfo() : @stat($dir);
		$usrInfo 	= $this->_getFileOwnerInfo($info);
		
		if (!empty($usrInfo)) 
		{
			$me = $this->_getChmodderInfo();
			
			$error .= '<br /> '.JText::_('Target file owner').': '.$usrInfo['user'].':'.$usrInfo['group'] .' ('.JText::_('user:group').')';
			$error .= '<br /> '.JText::_('Current script owner').': '. $me['user'].':'.$me['group'].' ('.JText::_('user:group').')';
			
			if ($usrInfo['user'] != $me['user'])
			{
				$error .= '<br /> '.JText::_('You probably need to change file owner. Please consult your hosting provider if you cannot do this');
			}
		}
		
		return $error;
	}
	
	/**
	 * Returns info about the 'chmodder' :) - FTP or Apache user:group
	 */
	function _getChmodderInfo() {
		static $me = 0;
		
		if (empty($me)) {
			$config = & JFactory::getConfig();
			
			$me = array(
				'user' => 'undefined',
				'group' => 'undefined'
			);
			
			if ($config->getValue('config.ftp_enable'))
			{
				$me = array(
					'user' => $config->getValue('config.ftp_user'),
					'group' => 'FTP User'
				);
			}
			else
			{
				$myInfo = array();
				$myInfo['uid'] = getmyuid();
				$myInfo['gid'] = getmygid();
				
				$res = $this->_getFileOwnerInfo($myInfo);
				
				if (!empty($res))
					$me = $res;
			}
		}
		
		return $me;
	}
	
	/**
	 * Check, whether we can chmod the path
	 * @param string $path
	 * @return boolean
	 */
	function canChmod($path = false)
	{
		if ($path == false)
			$path = $this->_filename;
		
		$perms = @fileperms($path);
		if ($perms !== false)
		{
			$f = new JD_File($path);
			
			if ($f->chmod($path, $perms ^ 0001))
			{
				$f->chmod($path, $perms);
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Checks existence of given file, or the file, represented by this object.
	 * @param mixed $filename
	 * @return boolean
	 */
	function exists($filename = false) {
		if (empty($filename)) {
			$filename = $this->_filename;
		}
		
		return file_exists($filename);
	}

	/**
	 * @access private
	 * @param $info array Stat data
	 */
	function _getFileOwnerInfo($info) 
	{
		$result = null;
		
		if (empty($info))
			return null;
		
		if (function_exists('posix_getpwuid')) {
			$result = array();
			
			if (!is_null($info['uid'])) {
				$usr 		= posix_getpwuid($info['uid']);
				$username 	= $usr['name'];
				$result['user'] = $username;
			}
			
			if (!is_null($info['gid'])) {
				$grp   		= posix_getgrgid($info['gid']);
				$groupname 	= $grp['name'];
				$result['group'] = $groupname;
			}
		}
		
		return $result;
	}
}