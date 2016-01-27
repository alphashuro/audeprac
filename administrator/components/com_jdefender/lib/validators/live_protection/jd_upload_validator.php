<?php
/**
 * $Id: jd_upload_validator.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined ('_JEXEC') or die('Access Restricted');

/**
 * Validates uploaded files
 * @author nurlan
 *
 */
class JD_Upload_Validator extends JD_Validator
{
	var $_executableFiles;
	
	function __construct() {
		parent::__construct('upload', 'live_protection');
		
		$this->_executableFiles = array();
	}
	
	function onFile($file, $contents = null) 
	{	
		if ($this->isInSkipRules($file, $contents))
			return;
		
		$rules = & $this->getRules();
		
		if (empty($rules['stop']) || !count($rules['stop']))
			return;
			
		foreach ($rules['stop'] as $rule) {
			if ($rule->check($file, $contents))
				$this->_executableFiles [] = array($rule->getRuleId(), $file);
		}
	}
	
	function onGetData() {
		return array($this->_name, $this->_type, &$this->_executableFiles);
	}
}