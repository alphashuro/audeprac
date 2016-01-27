<?php
/**
 * $Id: jd_none_rule.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined ('_JEXEC') or die('Access Restricted');

require_once JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_jdefender'.DS.'lib'.DS.'rules'.DS.'jd_abstract_rule.php';

/**
 * Empty rule class. Does not use 'rule' field.
 * Used for adding exception for variables not depending on their values
 * @author nurlan
 *
 */
class JD_None_Rule extends JD_Abstract_Rule
{
	function __construct($criteria) {
		parent::__construct($criteria);
	}

	/**
	 * Returns true, if the keys match
	 * @param $key
	 * @param $value
	 */
	function check($key, $value) {
		if (empty($key))
			return null;
		
		$rule = & $this->getRule();

		if ($rule->variable == $key || $rule->variable == '*' || $key == '*')
			return true;
	}
}