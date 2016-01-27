<?php
/**
 * $Id: jd_file_integrity_validator.php 7770 2012-01-26 12:39:15Z kostya $
 * $LastChangedDate: 2012-01-26 18:39:15 +0600 (Thu, 26 Jan 2012) $
 * $LastChangedBy: kostya $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined ('_JEXEC') or die('Restricted access');

class JD_File_Integrity_Validator extends JD_Validator
{	
	var $_fileData = null;
	
	var $_mismatches;
	
	var $_isFirstScan = false;
	
	var $_files;
	
	var $_dirs;
	
	function __construct() {
		parent::__construct('file_integrity', 'filesystem');
		
		jimport ('joomla.filesystem.file');
		jimport ('joomla.filesystem.folder');
		
		$this->_mismatches = array();
		$this->_files = array();
		$this->_dirs = array();
	}
	
	function setFirstScan($flag) {
		$this->_isFirstScan = !!$flag;
	}
	
	function onGetOptions() {
		return array(
			'readFile' => false
		);
	}
	
	// entodo: Check $contents data correctness, index hash_md
	function onFile(&$file) {
		// Do not validate symbolic links.
		if (is_link($file))
			return;
		
		return $this->_handleFile($file);
	}
	
	function onDir($dir) {
		// Take care of first scan
		return $this->_handleFile($dir, true);
	}
	
	function _handleFile($file, $isDir = false, $flush = false) {
		// Take care of first scan
		if ($this->_isFirstScan) 
		{
			$this->_mismatches [] = $file;
			return;
		}
		
		$data = null;
		$count = 0;
		$attribs = $this->_params->get('file_integrity_property_check', array());
		if(!is_array($attribs))
		{
			settype($attribs, 'array');
		}
		if ($isDir) {
			if ($file)
				$this->_dirs [$file] = 1;
			
			$count = count($this->_dirs);
			$data = & $this->_dirs;
			
			$attribs = array_merge($attribs, array('permission'));
			$attribs = array_diff($attribs, array('hash_md'));
		}
		else {
			if ($file)
				$this->_files [$file] = 1;
			
			$count = count($this->_files);
			$data = & $this->_files;
			
			$attribs = array_merge($attribs, array('permission', 'size'));
		}
			
		if ($flush || $count > 50) {
			$model = & JModel::getInstance('Filesystem', 'JDefenderModel');
			$files = $model->getFiles(array_keys($data));
			
			// Process
			if ($files) 
			{
				foreach ($files as $f)
				{
					$info = & JTable::getInstance('Filesystem', 'Table');
			
					// what attribs of files to check
					
					// get file info
					if (!$info->loadFromFile($f->fullpath)) {
						return;
					}
			
					// Compare files
					$mismatch = $info->compare($f, $attribs);
					if ($mismatch)
					{
						// File is changed.
						$info = new stdClass;
						$info->mismatch = $mismatch;
						$info->file = $file;
						$info->reason = 'changed';
						
						$this->_mismatches [] = $info;
					}
					
					// :)
					unset($data[ $f->fullpath ]);
				}
			}
			
			// New files
			if (count($data))
			{
				foreach ($data as $f => $dummy) {
					$info = & JTable::getInstance('Filesystem', 'Table');
					if ($info->loadFromFile($f))
					{
						$log = new stdClass;
						$log->file = $f;
						$log->reason = 'new';
						$log->mismatch = $info->compare(null, $attribs);
						
						
						$this->_mismatches [] = $log;
					}
				}
			}

			if ($isDir)
				$this->_dirs = array();
			else
				$this->_files = array();
		}
	}
	
	function onGetData() {
		// flush
		$this->_handleFile(false, false, true);
		$this->_handleFile(false, true, true);
		
		return array($this->_name, &$this->_mismatches);
	}
}