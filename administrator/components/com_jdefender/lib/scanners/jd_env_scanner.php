<?php
/**
 * $Id: jd_env_scanner.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined ('_JEXEC') or die('Access Restricted');

class JD_Env_Scanner extends JD_Scanner
{
	/**
	 * @return JD_Query_Scanner
	 */
	function & getInstance()
	{
		static $instance = null;
		
		if (empty($instance))
			$instance = new JD_Env_Scanner();

		return $instance;
	}
	
	/**
	 * @override
	 */
	function scan() {
		$request = JRequest::get('default', 2);
		
		foreach ($request as $key => $value)
			$this->applyRules($key, $value);
		
		$this->scanFiles();
		
		return $this->trigger('onGetData');
	}
	
	function scanFiles() {
		// Return if file scan is disabled.
		if (!$this->_jd_options->get('file_scan'))
			return; 
		
		$files = JRequest::get('files', 2);
		
		foreach ($files as $k => $v) {
			if (is_array($files[$k]['tmp_name'])) {
				foreach ($files[$k]['tmp_name'] as $key => $file) {
					$contents = @file_get_contents($files[$k]['tmp_name'][ $key ]);
					$this->trigger('onFile', array($k, $contents));
				}
			}
			else {
				$contents = @file_get_contents($files[$k]['tmp_name']);
				$this->trigger('onFile', array($k, $contents));
			}
		}
	}
	
	function applyRules($key, $value) {
		if (is_array($value)) {
			foreach ($value as $k => $v) 
			{
				$this->applyRules($key, $v);
			}
		}
		else {
			$this->trigger('onInstruction', array($key, $value));
		}
	}
	
	
	
	/**
	 * Loads validator(s)
	 * (non-PHPdoc)
	 * @see administrator/components/com_jdefender/lib/scanners/JD_Scanner#loadValidator($name)
	 * @override
	 */
	function loadValidator($names = false) {
		if (!$names) {
			$names = glob(JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_jdefender'.DS.'lib'.DS.'validators'.DS.'live_protection'.DS.'*.php');
			foreach ($names as $k => $v) {
				if (strpos($v, 'abstract') !== false) {
					unset($names[ $k ]);
					continue;
				}
				
				$names[ $k ] = basename($v);
			}
			
			$names = array_map(array(&$this, '_getValidatorName'), $names);
		}
		
		settype($names, 'array');
		
		parent::loadValidator($names, 'live_protection');
	}
	
	function _getValidatorName($filename) {
		return JFile::makeSafe(
			preg_replace(
				array('/^jd_/', '/_validator(?:.+)/'), 
				array('', ''), 
				$filename
			)
		);
	}
}