<?php
/**
 * $Id: jd_scanner.php 7770 2012-01-26 12:39:15Z kostya $
 * $LastChangedDate: 2012-01-26 18:39:15 +0600 (Thu, 26 Jan 2012) $
 * $LastChangedBy: kostya $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined('_JEXEC') or die('Restricted access');

/**
 * Abstract scanner.
 * @author Nurlan
 *
 */
jimport ('joomla.event.dispatcher');

class JD_Scanner extends JObject
{
	var $taskName;
	var $listeners;
	
	var $_jd_options;
	
	/**
	 * Gets the specific type of scanner. Must ve overriden
	 * @param $type
	 * @return JD_Scanner
	 */
	function & getInstance($type) {
		$className = JD_Scanner::loadScanner($type);
		
		if (!class_exists($className))
			return null;
		
		$res = call_user_func_array(array($className, 'getInstance'), array());
		return $res;
	}
	
	function __construct($taskName = 'All') {
		parent::__construct();
		
		$this->taskName 	= $taskName;
		$this->listeners 	= array();
		
		$model = & JModel::getInstance('Configuration', 'JDefenderModel');
		$this->_jd_options = new JParameter($model->getIni());
	}
	
	/**
	 * Register a validator
	 * @param $obj validator
	 * @return void
	 */
	function register(&$obj) {
		if (is_object($obj)) {
			for ($i = 0, $c = count($this->listeners); $i < $c; $i++) {
				if ($this->listeners[ $i ] == $obj)
					return;
			}
			$this->listeners[] = $obj;
		}
	}
	
	/**
	 * Trigger validator event
	 * @param $event string
	 * @param $args array
	 * @return array Event results
	 */
	function trigger($event, $args = array()) {
		$result = array();
		
		for ($i = 0, $c = count($this->listeners); $i < $c; $i++) {
			if (method_exists($this->listeners[ $i ], $event))
				$result [] = call_user_func_array(array(&$this->listeners[ $i ], $event), $this->makeValuesReferenced($args)); 
		}
		
		return $result;
	}
	/**
	 * 
	 * 	For PHP 5.3
	 * @param unknown_type $arr
	 */
	function makeValuesReferenced($arr){
		$refs = array();
		foreach($arr as $key => $value)
			$refs[$key] = &$arr[$key];
		return $refs;
	
	}
	
	/**
	 * Scans and returns array of log records.
	 * @return array
	 */
	function scan() {
		$this->loadScanner();
		
		$results = array();
		foreach ($this->getScannerNames() as $classname) {
			$scanner = new $classname();
			
			$results [] = $scanner->scan();
		}
		
		return $results;
	}
	
	/**
	 * 
	 * @return array Scanner names
	 */
	function getScannerNames($classnames = true) {
		static $scanners = 0;
		if (!$scanners) {
			$scanners = array();
			
			$dir = JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_jdefender'.DS.'lib'.DS.'scanners';
			
			jimport('joomla.filesystem.file');
			
			$curDir = getcwd();
			chdir( $dir );
			
			$classnames = array();
			$files = glob('*.php');
			
			for ($i = 0, $count = count($files); $i < $count; $i++ ) {
				if ($files[ $i ] == 'jd_scanner.php') {
					unset($files[ $i ]);
					continue;
				}
				$classnames[] = JFile::stripExt($files[ $i ]); 
			}
			
			chdir( $curDir );
			
			$names = array_map(array('JD_Scanner', '_cleanScannerName'), $files);
			
			$scanners['classnames'] = $classnames; 
			$scanners['names'] = $names; 
		}
		
		if ($classnames)
			return $scanners['classnames'];
		return $scanners['names'];
	}
	
	/**
	 * Loads Scanner implementations
	 * @param $name
	 * @return mixed filename or boolean
	 */
	function loadScanner($name = false) {
		jimport ('joomla.filesystem.file');
		if ($name)
			$name = JFile::makeSafe( $name );
		
		if (!$name) {
			$scanners = JD_Scanner::getScannerNames();
			
			$dir = JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_jdefender'.DS.'lib'.DS.'scanners';
			foreach ($scanners as $s)
				require_once $dir.DS.$s.'.php';
			
			return true;
		}
		
		$path = JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_jdefender'.DS.'lib'.DS.'scanners'.DS.'jd_'.$name.'_scanner.php';
		if (JFile::exists( $path )) {
			require_once $path;
			
			return 'JD_'.ucfirst($name).'_Scanner';
		}
		return false;
	}
	
	/**
	 * Loads validators
	 * @param $name validator name
	 * @return string classname of loaded validator
	 */
	function loadValidator($names, $type = '') 
	{
		foreach ($names as $name)
		{
			$classname 	= JFile::makeSafe('JD_'.ucfirst($name).'_Validator');
			$type 		= JFolder::makeSafe( $type );
			
			if ($type)
				$path = JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_jdefender'.DS.'lib'.DS.'validators'.DS.$type.DS;
			else
				$path = JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_jdefender'.DS.'lib'.DS.'validators'.DS;
			
			$path .= strtolower($classname).'.php';
			
			if (JFile::exists($path)) {
				include_once $path;
				
				if (class_exists($classname)) 
				{
					$validator = new $classname();
					$this->register( $validator );
				}
				else 
				{
					$logger = & JD_Logger::getInstance(__CLASS__);
					$logger->log('Failed loading validator, classname: '.$classname, 'warning');
				}
			}
		}
		
		return true;
	}
	
	/**
	 * Clean the scanner filename. Convert to name without prefixes
	 * @param $filename
	 * @return unknown_type
	 */
	function _cleanScannerName($filename) {
		return JFile::makeSafe(
			preg_replace(
				array('/^jd_/', '/_scanner(?:.+)/'), 
				array('', ''), 
				$filename
			)
		);
	}
}