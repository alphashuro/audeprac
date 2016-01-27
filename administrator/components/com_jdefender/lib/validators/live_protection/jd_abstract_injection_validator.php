<?php
/**
 * $Id: jd_abstract_injection_validator.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined ('_JEXEC') or die("Access Restricted");


class JD_Abstract_Injection_Validator extends JD_Validator
{
	var $_injections;
	
	function __construct($name, $type) {
		parent::__construct($name, $type);
		
		$this->_injections = array();
	}
	
	function onInstruction($name, $value) {
		if ($this->isInSkipRules($name, $value))
			return;
		
		$rules = & $this->getRules();
		
		if (empty($rules['stop']) || !count($rules['stop']))
			return; 
		
		foreach ($rules['stop'] as $rule) 
		{
			if ($rule->check($name, $value)) 
			{
				$this->_injections [] = array($rule->getRuleId(), $name, $value);
			}
		}
	}
	
	function onGetData() {
		return array($this->_name, &$this->_injections);
	}
}