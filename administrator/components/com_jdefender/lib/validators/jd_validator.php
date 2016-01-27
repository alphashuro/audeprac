<?php
/**
 * $Id: jd_validator.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined ("_JEXEC") or die('Restricted Access');

class JD_Validator extends JObject
{
	/**
	 * Validator parameters
	 * @var JParameter
	 */
	var $_params = null;
	
	/**
	 * Validator rules (#__jdefender_rules)
	 * @var unknown_type
	 */
	var $_rules = array();
	
	/**
	 * Validator type
	 * @var string
	 */
	var $_name;
	var $_type;
	
	function __construct($name, $type) {
		jimport ('joomla.filesystem.folder');
		jimport ('joomla.filesystem.file');
		
		$this->_name = JFolder::makeSafe($name);
		$this->_type = JFolder::makeSafe($type);
		
		$model = & JModel::getInstance('Configuration', 'JDefenderModel');
		
		$this->_params 	= new JParameter($model->getIni());
		
		$this->_rules = & JD_Rule_Manager::getRules($name);
	}
	
	/**
	 * Set validation rules
	 * @param $rules
	 */
	function setRules($rules) {
		$this->_rules = $rules;
	}
	
	function getRules() {
		return $this->_rules;
	}
	
	
	/**
	 * Event Handlers:
	 */
	
	/**
	 * 
	 * @param $name
	 * @param $value
	 */
	function onInstruction($name, $value) {
		
	}
	
	/**
	 * Called for each file 
	 * @param string $file The filename
	 * @return string
	 * @abstract
	 */
	function onFile( &$file ) {
		return false;
	}
	
	/**
	 * Called for each directory
	 * @param $dir string directory
	 * @return unknown_type
	 * @abstract
	 */
	function onDir( &$dir ) {
		return false;
	}
	
	/**
	 * Used to retrieve the scanned data
	 * @return unknown_type
	 * @abstract
	 */
	function onGetData() {
		return false;
	}
	
	/**
	 * Return the validator options
	 * @abstract
	 * @return array The options
	 */
	function onGetOptions() {
		return array();
	}
	
	/**
	 * Validation methods:
	 */
	
	
	/**
	 * 
	 * @param $key
	 * @param $value
	 * @return boolean
	 */
	function isInSkipRules($key, $value = null) {
		if (empty($this->_rules['ignore']))
			return false;
			
		for ($i = 0, $c = count($this->_rules['ignore']); $i < $c; $i++) {
			if ($this->_rules['ignore'][ $i ]->check($key, $value))
				return true;
		}
		
		return false;
	}
}