<?php
/**
 * $Id: jd_php_validator.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined('_JEXEC') or die("Restricted access");

/**
 * entodo: Add bad code check
 * @author nurlan
 *
 */
class JD_Php_Validator extends JD_Validator
{
	var $badFiles;
	var $jexec;
	
	function __construct() {
		parent::__construct('php', 'filesystem');
		
		$this->badFiles= array();
		$this->jexec = array();
		
		jimport ('joomla.filesystem.file');
	}
	
	function onGetOptions() {
		// Request the file contents from scanner.
		return array(
			'readFile' => true
		);
	}
	
	function onFile(&$file, &$contents) {
		if (strtolower(JFile::getExt($file)) != 'php')
			return;
			
		if (empty($contents))
			return;

		if(in_array(JString::strtolower($file), array('index.php', 'index2.php', 'configuration.php', 'index3.php')))
		{
				return;
		}
		$jexec = $this->_isNoJexec($contents);
	
		if ($jexec)
			$this->jexec [] = &$file;
		 
		$badFunc = $this->_checkForBadFunctions($contents);
		
		if ($badFunc) {
			$node = new stdClass;
			$node->functions = $badFunc;
			$node->file = & $file;
			
			$this->badFiles [] = $node;
		}
	}
	
	function onGetData() {
		return array($this->_name, $this->jexec, $this->badFiles);
	}
		
	
	function _checkForBadFunctions(&$contents) {
		if (!$this->_params->get('php_scan_bad_functions'))
			return false;
		
		$funcs = array(
//			'/`.*`/mis', 						// backtick
			'/\bsystem\s*\(.*\)/misU', 			// system
			'/\bexec\s*\(.*\)/misU', 			// exec() - Execute an external program
			'/\bpassthru\s*\(.*\)/misU', 		// passthru() - Execute an external program and display raw output 
			'/\bpopen\s*\(.*\)/misU', 			// popen() - Opens process file pointer 
			'/\bpcntl_exec\s*\(.*\)/misU',		// pcntl_exec() - Executes specified program in current process space
			'/\bshell_exec\s*\(.*\)/misU', 		// shell_exec - Execute command via shell and return the complete output as a string
			'/\bproc_open\s*\(.*\)/misU',		// proc_open() - Execute a command and open file pointers for input/output 
			'/\bproc_terminate\s*\(.*\)/misU', 	// proc_teminate
			'/\bpclose\s*\(.*\)/misU',			// pclose
		//	'/\bdl\s*\(.*\)/misU',				// dl
			'/\bposix_kill\s*\(.*\)/misU',		// posix_kill
			'/\bposix_mkfifo\s*\(.*\)/misU'		// posix_mkfifo - Create a fifo special file (a named pipe)
		);
		
		$return = array();
		$results = array();
		
		foreach ($funcs as $func) {
			if (preg_match_all($func, $contents, $results, PREG_OFFSET_CAPTURE)) {
				foreach ($results[0] as $result) {
					$pos 	= $result[ 1 ];
					$line 	= preg_replace('/[^\n]*/', '', substr($contents, 0, $pos));
	
					$return [] = 'Line #'.strlen($line).': '.$result[ 0 ];
				}
			}
		}
		
		return count($return) ? $return : false;
	}
	
	/**
	 * Checks if 'defined('_JEXEC') or die' exists in a file
	 * @param $contents File contents
	 * @return boolean
	 */
	function _isNoJexec(&$contents) {
		if (!$this->_params->get('php_scan_jexec'))
			return false;
		
		return !preg_match('/(?:<\?php|<\?).+?defined\s*\(\s*(?:\'|")_JEXEC(?:\'|")\s*\)\s*or\s*die/mis', $contents)
			&& !preg_match('/return\s+_il_exec\(\.*\).*;/', $contents); // ioncube check.
	}
}