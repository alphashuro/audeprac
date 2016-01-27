<?php
/**
 * $Id: jd_permission_action.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined ('_JEXEC') or die('Restricted Access');

require_once JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_jdefender'.DS.'lib'.DS.'actions'.DS.'jd_file_integrity_changed_action.php';

class JD_Permission_Action extends JD_File_Integrity_Changed_Action
{	
	function __construct() {
		parent::__construct();
		
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
	}
	
	function fix() {
		if (empty($this->_fixes))
			return 0;
		list($files, $ids) = $this->_getFilesAndIds($this->_fixes);
		
		$config 	= & $this->getConfig();
		$filePerms 	= intval($config->get('permission_max_file_permission', '644'), 8);
		$dirPerms 	= intval($config->get('permission_max_dir_permission', 	'755'), 8);
		
		
		$chmoddedFiles = array();
		$rollback = false;
		
		@clearstatcache();
		
		foreach ($files as $file) 
		{
			if (!JFile::exists($file) && !JFolder::exists($file))
				continue;
			
			$info = new stdClass;
			$info->perms = fileperms($file);
			$info->file = $file;
			
			$isFile = is_file($file);
			$isDir 	= is_dir($file);
			
			if ($isFile || $isDir) 
			{
				$f = new JD_File($file);
				
				$perms = $isFile ? $filePerms : $dirPerms;
				
				if ($f->chmod($perms) === false) {
					$this->setError($f->getError());
					$rollback = true;
					break;
				}
			}
						
			$chmoddedFiles[] = $info;
		}

		if ($rollback)
		{
			foreach ($chmoddedFiles as $info) {
				$f = new JD_File($info->file);
				$f->chmod($info->perms);
			}
			
			$this->_fixes = array();
			return false;
		}
		
		parent::setLogStatus($ids, 'fixed');
		parent::refreshFilesystemTable($files);
		
		$this->_fixes = array();
		
		return count($chmoddedFiles);
	}
}