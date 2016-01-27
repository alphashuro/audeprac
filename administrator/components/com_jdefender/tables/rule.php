<?php
/**
 * $Id: rule.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined ('_JEXEC') or die("Restricted Access");


class TableRule extends JTable
{
	var $id;
	
	var $component = '*';
	
	var $variable;
	/**
	 * Rule body
	 * @var unknown_type
	 */
	var $rule;
	/**
	 * Action to perform: stop|ignore|filter
	 * @var string
	 */
	var $action;
	/**
	 * Rule family: php_injection|sql_injection
	 * @var string
	 */
	var $family;
	/**
	 * Rule class - preg|equality|none
	 * @var string
	 */
	var $type; 
	/**
	 * Is created by user? 0 - Mighty, 1 - User, 2 - Third party. No other values are used.
	 * @var boolean
	 */
	var $origin;
	
	var $ctime;
	/**
	 * Mighty ID
	 * @var integer
	 */
	var $mid = 0;
	
	var $published = 1;
	
	/**
	 * Rule version for third party rules.
	 * @var unknown_type
	 */
	var $version;
	
	function __construct(& $db) {
		parent::__construct('#__jdefender_rules', 'id', $db);
	}
}