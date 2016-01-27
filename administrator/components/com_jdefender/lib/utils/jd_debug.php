<?php
/**
 * $Id: jd_debug.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined ('_JEXEC') or die("Restricted Access");


/**
 * Debug utility class
 * @author nurlan
 *
 */
class JD_Debug extends JObject 
{
	function __construct($debug = true) {
		parent::__construct();
		
		$this->_log 	= array();
		$this->_debug 	= !!$debug;
	}
	
	var $_debug = true;
	
	var $_log = null;
	
	/**
	 * 
	 * @return JD_Debug
	 */
	function & getInstance() {
		static $instance = 0;
		
		if (empty($instance)) {
			$instance = new JD_Debug(true);
		}
		
		return $instance;
	}
	
	/**
	 * Dump the passed parameters 
	 */
	function dump() {
		if (!$this->_debug)
			return;
		
		$args = func_get_args();
		
		echo '<pre>';
		var_dump($args);
		echo '</pre>';
		
		
		$ex = new JException('Stack');
		
		echo '<pre>';
		var_dump($ex->getTraceAsString());
		echo '</pre>';
	}
	
	/**
	 * Dump the parameters and die
	 * @return unknown_type
	 */
	function dumpDie() {
		if (!$this->_debug)
			return;
		
		$args = func_get_args();
		
		echo '<pre>';
		var_dump($args);
		echo '</pre>';
		
		
		$ex = new JException('Stack');
		
		echo '<pre>';
		var_dump($ex->getTraceAsString());
		echo '</pre>';
		
		die;
	}
	
	/**
	 * Add log string
	 * @param $str
	 * @return unknown_type
	 */
	function addLog($str) {
		$this->_log [] = $str;
	}
	
	/**
	 * Get log as a string
	 * @param boolean $flush
	 * @return string
	 */
	function getLog($flush = true, $separator = '<br />', $echo = true) {
		$return = implode($separator, $this->_log);
		if ($flush)
			$this->_log = array();
		
		if ($echo)
		{
			echo $return;
			return;
		}
			
		return $return;
	}
	
	/**
	 * Write the log to file
	 * @param $name The log file
	 * @param $deleteOld boolean
	 * @return void
	 */
	function writeLogFile($name, $deleteOld = false) {
		if (!$name) {
			$this->setError('Bad log file name');
			return false;
		}
		
		$logFile = JPATH_ROOT.DS.'logs'.DS.JFile::makeSafe($name);
		
		$oldData = '';
		if (!$deleteOld && JFile::exists($logFile))
			$oldData = JFile::read($logFile);
		
		JFile::write($logFile, $lodData."\n\n=========================================\n".$this->getLog(true, "\n"));
	}
	
	/**
	 * Echo log and die
	 * @return unknown_type
	 */
	function getLogDie() {
		$this->dumpDie($this->_log);
	}
}