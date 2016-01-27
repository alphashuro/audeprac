<?php
/**
 * $Id: scan.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined('_JEXEC') or die ('Restricted Access');

jimport('joomla.application.component.controller');

class JDefenderControllerScan extends JController
{
	function __construct()
	{
		parent::__construct();
	}
	
	function display()
	{
		JRequest::setVar('view', 'scan');
		parent::display();
	}
	
  	function cancel()
 	{
  		$this->setRedirect( 'index.php?option=com_jdefender&controller=scan' );
  	}

  	
  	function scan($doLog = false) {
  		global $mainframe;
  		
  		$session 	= & JFactory::getSession();
  		$confModel 	= & JModel::getInstance('Configuration', 'JDefenderModel');
		$componentConfig = $confModel->getData(false);
		
		// Turn off error reporting
		JD_Error::unsetErrorHandlers();
		
		jimport ('joomla.error.log');
		jimport ('joomla.filesystem.file');
		
		// choose scan type
  		$filesystemScanned 	= $session->get('filesystem.scanned', 	false, 'jdefender');
  		$optionsScanned 	= $session->get('options.scanned', 		false, 'jdefender');
  		
  		
		// Scan now
  		$scanModel = & JModel::getInstance('Scan', 'JDefenderModel');
  		$scanModel->setState('filesystem.scanned', 	$filesystemScanned);
  		$scanModel->setState('options.scanned', 	$optionsScanned);
  		
  		$scanData = $scanModel->getScanData('', $doLog);
  		
  		  		
  		if ($doLog)
  			JD_Vars_Helper::setVar('status', 'jdefender_scan', JText::_('Processing Scan Results'));
  		
  		foreach ($scanData as $family => $data)
  		{
  			// skip empty data
  			if (!$data)
  				continue;
  			
  			if ($family == 'filesystem') {
	  			if ($scanModel->_isFirstScan()) {
	  				// The handler for the first scan which does not write logs for new files.
  					$firstScanHandler = JD_Handler::getInstance('first_scan', 'filesystem');
	  				$firstScanHandler->handleResults($data);
	  				$firstScanHandler->flushLogs();
  				}
  			}
  			
  			foreach ($data as $type => $results) {
	  			$handler = JD_Handler::getInstance($type, $family);
	  		  	if ($handler) 
	  		  	{
	  		  		if ($doLog) {
	  		  			$titles = JD_Log_Helper::readableLogType($type);
	  		  			if ($titles) 
	  		  				JD_Vars_Helper::setVar('status', 'jdefender_scan', JText::_('Processing Scan Results').': '.$titles->title);
	  		  		}
	  		  		
			  		$handler->handleResults($results);
			  		$handler->flushLogs();
	  		  	}
  			}
  		}
  		
  		// Turn on error reporting
  		JD_Error::putErrorHandlersBack();
  		
  		$state = array($scanModel->getState('filesystem.scanned'), $scanModel->getState('options.scanned'));
  		
  		// save scan state to session
  		$session->set('filesystem.scanned', $state[ 0 ], 'jdefender');
  		$session->set('options.scanned', 	$state[ 1 ], 'jdefender');
  		
  		return $state;
  	}
  	
  	function createScanFileList() {
		$doLog = JD_Scan_Helper::isLogging();
		// disable logging for now
		JD_Scan_Helper::setLogging(false);
		
  		$fsScanner = & JD_Scanner::getInstance('filesystem');
  		
  		// Register a validator, to form a filelist.
  		$fileListCreator = new JD_Filelist_Creator();
  		$fsScanner->register($fileListCreator);
  		
  		// Make the file list
  		$results = $fsScanner->scan(JPATH_ROOT, false);
  		
  		$filename = $this->_getFileListName();
  		
  		// Write the file list to a temporary file 
  		file_put_contents($filename, $results['filelist'][ 1 ]);
  		
  		
  		// Set filelist filename for scan process
  		JD_Scan_Helper::setFilelist($filename);
  		JD_Scan_Helper::setLogging($doLog);
  		
  		// Store total file number
  		JD_Vars_Helper::setVar('total', 'jdefender_scan', $fsScanner->_filesScanned + $fsScanner->_foldersScanned);
  		
  		return array($fsScanner->_filesScanned, $fsScanner->_foldersScanned);
  	}
  	
  	/**
  	 * @return string Filelist filename
  	 */
	function _getFileListName() {
		static $fileList = null;
		
		if ($fileList)
			return $fileList;
		
		$uid = uniqid('scan');
		$fileList = false;
		
		// Filenames to try
		$fnames = array(
			JPATH_ROOT.DS.'tmp'.DS.$uid,
			JPATH_ROOT.DS.'cache'.DS.$uid,
			JPATH_ROOT.DS.'logs'.DS.$uid,
			'/tmp/'.$uid
		);
				
		foreach ($fnames as $fname) {
			$hFileList = @fopen($fname, 'w');
			if ($hFileList) {
				$fileList = $fname;
				@fclose($hFileList);
				break;
			}
		}
		
		if ($fileList === false) {
			$this->setError(JText::_('None of the folders are writable').': '.JPATH_ROOT.DS.'tmp, '.JPATH_ROOT.DS.'cache, '.JPATH_ROOT.DS.'logs, /tmp');
		}
			
		return $fileList;
	}
}

/**
 * A class that is used only for creating list of files to be scannned.
 * Implements JD_Validator methods. 
 * @author nurlan
 *
 */
class JD_Filelist_Creator extends JObject
{
	function __construct() {
		parent::__construct();
		$this->_filesystemList = array();
	}
	
	/**
	 * List of filenames
	 * @var array
	 */
	var $_filesystemList;
	
	/**
	 * Filesystem scanner event handlers
	 * @param $file
	 */
	function onFile($file) {
		$this->_filesystemList [] = $file;
	}
	
	/**
	 * Filesystem scanner event handlers
	 * @param $dir
	 */
	function onDir($dir) {
		$this->_filesystemList [] = $dir;
	}
	
	function onGetData() {
		return array('filelist', implode("\n", $this->_filesystemList));
	}
}