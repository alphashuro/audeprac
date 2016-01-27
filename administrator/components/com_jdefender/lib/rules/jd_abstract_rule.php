<?php
/**
 * $Id: jd_abstract_rule.php 7311 2011-08-19 11:18:02Z shitz $
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
 * Class to handle various rules. Performs direct checking
 * @author nurlan
 * @abstract
 */
class JD_Abstract_Rule extends JObject
{
	/**
	 * 
	 * @var TableRule
	 */
	var $_rule;
	
	/**
	 * 
	 * @param TableRule $rule
	 */
	function __construct($rule = null) {
		parent::__construct();
		$this->_rule = $rule;
	}
	
	/**
	 * @return TableRule
	 */
	function & getRule() {
		return $this->_rule;
	}
	
	/**
	 * @return int Rule id
	 */
	function getRuleId() {
		return @$this->_rule->id;
	}
	
	/**
	 * Check the rule
	 * @param string $key
	 * @param string $value
	 * @return mixed Null or boolean. Null, if value is empty
	 * @abstract
	 */
	function check($key, $value) {
		JError::raiseError(404, 'JD_Abstract_Rule::check not implemented');
	}
}