<?php
/**
 * $Id: filesystem.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined('_JEXEC') or die('Restricted Access');

jimport('joomla.application.component.model');

class JDefenderModelFilesystem extends JModel
{
	var $_lastScanData;
	
	var $_lastScanDate;
	
	var $_total;
	
	
	function __construct() {
		parent::__construct();
		
		$this->_lastScanData = array();
	}
	
	/**
	 * Fetch the files by filenames
	 * @param array $filenames Filenames (fullpath)
	 * @return array
	 */
	function getFiles($filenames = false) {
		if (empty($filenames))
			return null;
			
		settype($filenames, 'array');
		
		foreach ($filenames as $k => $v) {
			$filenames[ $k ] = $this->_db->Quote( $v );
		}
		
		$q = 'SELECT * FROM #__jdefender_filesystem WHERE `fullpath` IN ( '.implode(', ', $filenames).' )';
		
		$this->_db->setQuery( $q );
		return $this->_db->loadObjectList('fullpath');
	}
	
	/**
	 * 
	 * @param $filenames
	 * @return unknown_type
	 */
	function rehashFiles($files, $useFilter = false) {
		if (empty($files))
			return;
		
		settype($files, 'array');
		
		$filter	= null;
		// Create the filesystem filter.
		if ($useFilter)
		{
			// Fetch component config
			$confModel 	= & JModel::getInstance('Configuration', 'JDefenderModel');
			$config	= new JParameter($confModel->getIni());
		
			$fileExts 		= explode("\n", trim($config->get('scan_file_patterns', '*'), "\r\n "));
			$fileExts 		= str_replace(array("\n", "\r\n", "\n\r"), '', $fileExts);
			
			$excludedDirs 	= explode("\n", trim($config->get('scan_excluded_directories', (JPATH_ROOT.DS.'cache' ."\n". JPATH_ROOT.DS.'tmp')), "\r\n "));
			$excludedDirs 	= str_replace(array("\n", "\r\n", "\n\r"), '', $excludedDirs);
			
			for ($i = 0, $count = count($excludedDirs); $i < $count; $i++)
				$excludedDirs[ $i ] = JPath::clean(JPATH_ROOT.DS.$excludedDirs[ $i ]);
		
			$filter = new JD_Filesystem_Filter($fileExts, $excludedDirs);
		}
		
		$this->deleteFiles($files);
		$count = $this->addFiles($files, $filter);
		
		return $count;
	}
	
	function addFiles($files, $filter = false) {
		$db = & JFactory::getDBO();
		$data = $this->_filesToInfoStructs($files, $filter);
		
		$i = 0;
		$rowsPerQuery = 15;
		$count = count($data);
		if ($count < $rowsPerQuery)
			$rowsPerQuery = $count;
		
			
		$fields = array('id', 'filename', 'fullpath', 'permission', 'ctime', 'mtime', 'size', 'type', 'gid', 'uid', 'hash_md', 'contents', /*, 'scandate'*/);
		$insert = 'INSERT INTO #__jdefender_filesystem (`id`, `filename`, `fullpath`, `permission`, `ctime`, `mtime`, `size`, `type`, `gid`, `uid`, `hash_md`, `contents`, `scandate`) ';
		
		$now = & JFactory::getDate();
		$bodies = array();
		
		foreach ($data as $entry) {
			$part = array();
			
			foreach ($fields as $f) 
				$part [] = $db->Quote(@$entry->$f);
			$part [] = $db->Quote($now->toMySQL());
				
			$bodies [] = '('.implode(', ', $part).')';
			
			$i++;
			
			if ($i && $i % $rowsPerQuery == 0) {
				$query = $insert.' VALUES '.implode(', ', $bodies);
				
				$db->setQuery($query);
				$db->query();
				
				$bodies = array();
			}
		}
		
		if (count($bodies)) {
			$query = $insert.' VALUES '.implode(', ', $bodies);
			
			$db->setQuery($query);
			$db->query();
		}
	}
	
	function _filesToInfoStructs($files, $filter = false) {
		$fileReader = & JTable::getInstance('Filesystem', 'Table');
		settype($files, 'array');
		
		// Process files
		$files 		= array_unique($files);
		$fileInfo 	= array();
		
		foreach ($files as $file) {
			if ($filter && !$filter->isFileOK($file, true))
				continue;
			
			// Load file data with contents
			if ($fileReader->loadFromFile($file, true)) {
				$fileReader->id = null;
				$fileInfo [] 	= clone($fileReader);
			}
		}
		
		return $fileInfo;
	}
	
	/**
	 * Deletes files from filesystem table
	 * @param $filenames
	 * @return unknown_type
	 */
	function deleteFiles($filenames = false) {
		settype($filenames, 'array');
		
		foreach ($filenames as $k => $v) {
			$filenames[ $k ] = $this->_db->Quote( $v );
		}
		
		$q = 'DELETE FROM #__jdefender_filesystem WHERE `fullpath` IN ( '.implode(', ', $filenames).' )';
		
		$this->_db->setQuery( $q );
		$this->_db->query();
		
		return $this->_db->getAffectedRows();
	}
	
	/**
	 * Returs the scan data. Depends on $this->getState('scandata.limitstart'), $this->getState('scandata.limit')
	 * @return unknown_type
	 */
	function & getScanData() {
		static $_lastScanData = array();
		
		$limitstart = (int)$this->getState('scandata.limitstart');
		$limit 		= (int)$this->getState('scandata.limit');
		
		if ( ! array_key_exists($limit.'_'.$limitstart, $_lastScanData) )
		{
			$this->_db->setQuery('SELECT * FROM #__jdefender_filesystem', $limitstart, $limit);
			$_lastScanData[$limit.'_'.$limitstart] = $this->_db->loadObjectList('fullpath');
		}
		
		return $_lastScanData[$limit.'_'.$limitstart];
	}
	
	function getScanDataTotal() {
		if (empty($this->_total)) {
			$q = 'SELECT COUNT(*) FROM #__jdefender_filesystem';
			$this->_db->setQuery( $q );
			$this->_total = $this->_db->loadResult();
		}
		return $this->_total;
	}
	
	function getLastScanDate() {
		if (empty($this->_lastScanDate)) {
			$db = & JFactory::getDBO();
			
			$db->setQuery('SELECT MAX(`scandate`) FROM #__jdefender_filesystem');
			$this->_lastScanDate = $db->loadResult();
		}
		return $this->_lastScanDate;
	}
}