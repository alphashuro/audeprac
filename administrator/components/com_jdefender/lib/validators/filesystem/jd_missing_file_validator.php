<?php
/**
 * $Id: jd_missing_file_validator.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined ('_JEXEC') or die('Restricted Access');

class JD_Missing_File_Validator extends JD_Validator
{
	var $_filesystemData;
	
	var $_files;
	
	var $_missingFiles;
	
	function __construct() {
		parent::__construct('missing_file', 'filesystem');
		
		$this->_filesystemData = null;
		$this->_files = array();
		$this->_missingFiles = array();
		
		jimport ('joomla.filesystem.file');
	}
	
	function onGetData() {
		// flush :)
		$this->_handleFile(false, true);
		
		return array(&$this->_name, &$this->_missingFiles);
	}
	
	function onFile(&$file) {
		$this->_handleFile($file);
	}
	
	function onDir(&$dir) {
		$this->_handleFile($dir);
	}
	
	function _handleFile($file, $force = false) {
		if ($file)
			$this->_files[$file] = 1;
		
		if ($force || count($this->_files) > 50) {
			$model = & JModel::getInstance('Filesystem', 'JDefenderModel');
			$files = $model->getFiles(array_keys($this->_files));
			
			if ($files) {
				foreach ($files as $f)
					unset($this->_files[$f->fullpath]);
				
				if (count($this->_files))
					$this->_missingFiles = array_merge($this->_missingFiles, array_keys($this->_files));
			}
			
				
			$this->_files = array();
		}
	}
}