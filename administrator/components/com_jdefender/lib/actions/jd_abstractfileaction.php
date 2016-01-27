<?php
/**
 * $Id: jd_abstractfileaction.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined ('_JEXEC') or die("Bye");

require_once JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_jdefender'.DS.'lib'.DS.'actions'.DS.'jd_action.php';

class JD_AbstractFileAction extends JD_Action
{
	function __construct() {
		parent::__construct();
	}
	
	
	
	/**
	 * Delete the files, and removes them from filesystem table, marks them in the log as 'deleted'
	 * Note that processed items are removed from buffer
	 * @return int|boolean number of deleted files, FALSE on error
	 */
	function delete() {
		$count = 0;
		
		if (!empty($this->_deleteLogs)) {
			list($files, $ids) = $this->_getFilesAndIds($this->_deleteLogs);
			
			foreach ($files as $file) {
				if (JFile::exists($file)) {
					if (!JFile::delete($file)) {
						$this->setError(JText::_('Cannot delete file').': '.$file);
						$this->_deleteLogs = array();
						
						return false;
					}
					$count++;
				}
			}
			
			if (count($ids)) {
				$this->setLogStatus($ids, 'deleted');
			}
			
			$this->removeFromFilesystemTable($files);
			$this->setLogStatus($ids, 'deleted');
		}
		
		// Empty the buffer
		$this->_deleteLogs = array();
		
		return true;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see components/com_jdefender/lib/actions/JD_Action#quarantine()
	 */
	function quarantine() {
		jimport ('joomla.filesystem.file');
		
		$config = & $this->getConfig();
		
		$quarantineDir = JPath::clean($config->get('dir_quarantine'));
		
		if (!JFolder::exists($quarantineDir))
		{
			JFolder::create($quarantineDir);
		}
		
		if (empty($quarantineDir) || !JFolder::exists($quarantineDir))
		{
			JError::raiseWarning('ME_D', JText::_('Please set the Quarantine Directory'));
			$this->setError(JText::_('Quarantine directory is not defined'));
			$this->_quarantines = array();
			
			return false;
		}
		
		// Get the files to quarantine
		list($files, $ids) = $this->_getFilesAndIds($this->_quarantines);
		
		// Move 'em all ;)
		$quarantinedFiles 	= array();
		$filesToRehash 		= array();
		foreach ($files as $file) 
		{
			$file = JPath::clean($file);
			if (JFile::exists($file)) {
				$destFile = str_replace(JPATH_ROOT, '', $file);
				$destFile = trim($destFile, DS.' ');
				
				$destFile = JPath::clean($quarantineDir.DS.$destFile);
				
				$filesToRehash [] = $file;
				$filesToRehash [] = $destFile;
				
				if (JFile::exists($destFile))
				{
					JFile::raiseWarning(JText::_('The file has been already quarantined').': '.$destFile);
					$this->setError(JText::_('The file has been already quarantined').': '.$destFile);
					return false;
				}
				
				$info = new stdClass;
				$info->original 	= $file;
				$info->quarantined 	= $destFile;
				$info->moved 		= false;
				
				$quarantinedFiles[] = $info;
			}
		}
		
		$rollback = false;
		
		foreach ($quarantinedFiles as $k => $info) {
			if (!JFolder::exists(dirname($info->quarantined))) 
			{
				JFolder::create(dirname($info->quarantined));
			}
			
			if (!JFile::move($info->original, $info->quarantined)) {
				$rollback = true;
				$this->setError(JText::_('Cannot move file').' '.$info->original.' '.JText::_('to').' '.$info->quarantined);
				
				break;
			}
			$quarantinedFiles[ $k ]->moved = true;
		}
		
		if ($rollback) 
		{
			// Rollback the changes
			foreach ($quarantinedFiles as $k => $info) {
				if ($info->moved)
					JFile::move($info->quarantined, $info->original);
			}

			$this->_quarantines = array();
			return false;
		}
		
		$this->setLogStatus($ids, 'deleted');
		$this->refreshFilesystemTable($filesToRehash, true);
		
		$this->_quarantines = array();
		
		return true;
	}
}