<?php
/**
 * $Id: jd_rule_manager.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined ('_JEXEC') or die('Restricted Access');


class JD_Rule_Manager extends JObject
{
	var $rules;
	
	function __construct() {
		parent::__construct();
		
		$this->rules = array(); 
		$this->_load();
	}
	
	/**
	 * @return JD_Rule_Manager singletone
	 */
	function & getInstance() 
	{
		static $instance = 0;
		
		if (empty($instance)) {
			$instance = new JD_Rule_Manager();
		}
		
		return $instance;
	}
	
	/**
	 * Factory method. Returns a new instance, depending on $rule's type
	 * @abstract
	 * @param TableRule $rule
	 */
	function constructRule($rule) {
		$classname = 'JD_'.$rule->type.'_Rule';
		
		if (class_exists($classname))
			return new $classname($rule);
	}
	
	function _load() {
		static $loaded = 0;
		if ($loaded)
			return;
		else
			$loaded = true;

		
		$dir = JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_jdefender'.DS.'lib'.DS.'rules';
		
		foreach (glob($dir.DS.'jd_*.php') as $file) {
			require_once $file;
		}
		
		$this->rules = array();
		
		$rules = & $this->_getRules();
		
		foreach ($rules as $r)
			$this->rules[$r->family][$r->action][] = $this->constructRule($r);	
	}
	
	/**
	 * Fetchs rules.
	 * @param string $validator The validator name
	 * @return mixed
	 */
	function & getRules($validator = false) {
		if (empty($validator))
			return $this->rules;
		
		if (!empty($this->rules[$validator]))
			return $this->rules[$validator];
		
		$result = array();
		return $result;
	}
	
	function & _getRules() {
		$model = & JModel::getInstance('Rule', 'JDefenderModel');
		
		$model->setState('component', JRequest::getCmd('option'));
		$model->setState('published', 1);
		
		$rules = & $model->getDataAsTables();
		
		return $rules;
	}
}