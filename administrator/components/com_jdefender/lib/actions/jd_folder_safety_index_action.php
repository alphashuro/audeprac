<?php
/**
 * $Id: jd_folder_safety_index_action.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined ( "_JEXEC") or die("Restricted acception");

class JD_Folder_Safety_Index_Action extends JD_Action
{
	function __construct() {
		parent::__construct();
		
		jimport ('joomla.filesystem.file');
		jimport ('joomla.filesystem.folder');
		
		$config = & $this->getConfig();

		$this->indexHTMLContents = $config->get('folder_safety_index_contents', '<html><head></head><body></body></html>');
	}
	
	var $indexHTMLContents; 
	
	function fix() {
		if (empty($this->_fixes))
			return 0;
			
		list($files, $ids) = $this->_getFilesAndIds($this->_fixes);
		
		$newFiles 	= array();
		$rollback 	= false;
		
		foreach ($files as $dir)
		{
			if (!is_dir($dir)) 
				continue;
			
			$indexFile = JPath::clean($dir.DS.'index.html');
			
			if (JFile::exists($indexFile))
				continue;
				
			$fixFile = new JD_File($indexFile);
			
			
			
			// Try to write the file
			if (!$fixFile->write($this->indexHTMLContents)) {
				$this->setError($fixFile->getError());
				$rollback = true;
				break;
			}
			
			$newFiles [] = $indexFile;
		}
		
		if ($rollback) {
			foreach ($newFiles as $file) {
				if (JFile::exists($file))
					JFile::delete($file);
			}
			
			$this->_fixes = array();
			
			return false;
		}
		
		parent::refreshFilesystemTable($newFiles);
		parent::setLogStatus($ids, 'fixed');
		
		$this->_fixes = array();
		
		return count($newFiles);
	}
}