<?php
/**
 * $Id: jd_log_reader.php 7311 2011-08-19 11:18:02Z shitz $
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
class JD_Log_Reader extends JObject
{
	var $_logRecord;
	var $_filesystemState;
	
	
	function __construct() {
		parent::__construct();
	}
	
	/**
	 * Load the log readers
	 * @static
	 * @return unknown_type
	 */
	function loadReaders() {
		$path = JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_jdefender'.DS.'lib'.DS.'log_readers';
		$files = glob($path.DS.'jd_*_log_reader.php');
		if (is_array($files)) {
			foreach ($files as $f)
				require_once $f;
		}
		return true;
	}
	
	/**
	 * Returns a NEW log reader instance.
	 * @param $type string type name
	 * @static
	 * @return JD_Log_Reader
	 */
	function & getInstance($type) {
		static $genericReader 	= 0;
		
		JD_Log_Reader::loadReaders();
		$className = 'JD_'.ucfirst($type).'_Log_Reader';
		if (!class_exists($className)) {
			if (empty($genericReader))
				$genericReader = new JD_Log_Reader();
			return $genericReader;
		}
		$instance = new $className();
		
		return $instance;
	}
	
	/**
	 * Returns the data as a table
	 * @abstract
	 */
	function getTables() {
		return array(array());
	}
	
	/**
	 * Sets actual filesystem entry
	 * @param $logRecord
	 */
	function setRecord($logRecord) {
		$this->_logRecord = $logRecord;
	}
	
	/**
	 * 
	 * @param $currentState
	 */
	function setCurrentState($currentState) {
		$this->_filesystemState = $currentState;
	}
	
	/**
	 * Returns modified log record, with 'data' attribute, holding assoc array of values to display
	 * @param $data The data stored in the 'post' field of 'log' table
	 * @return unknown_type
	 * @deprecated
	 */
	function read($logRecord = null) {
		if (is_null($logRecord))
			$logRecord = & $this->_logRecord;
		
		if (empty($logRecord->post))
			return null;
		
		if (!is_array($logRecord->post))
			$data = @unserialize($logRecord->post);
		else
			$data = $logRecord->post;
			
		return $data;
	}
	
	/**
	 * Read the current state of item 
	 * @param $state
	 * @return unknown_type
	 * @deprecated
	 */
	function readCurrentState($state) {
		return null;
	}
}