<?php
/**
 * $Id: log.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined('_JEXEC') or die('Restricted Access');
jimport('joomla.filter.input');

/**
 * JDefender Log Table class
 *
 * @package		Joomla
 * @subpackage	JDefender
 * @since 1.0
 */
class TableLog extends JTable
{

	var $id      = null;       //int
	var $type    = null;       //string
	var $user_id = null;       //int
	var $url     = null;       //string
	var $post    = null;       //string
	var $cook    = null;       //string
	var $referer = null;       //string
	var $status  = null; 	   //string
	var $issue;
	
	var $ip      = null;       //string
	var $ctime   = null;       //string
	var $user_agent = null;
	var $total;
	
	var $extension;

	function __construct(& $db) {
		parent::__construct('#__jdefender_log', 'id', $db);
	}
	
	
	function check() {
		if (empty($this->type)) {
			$this->setError(JText::_('Type not set'));
			return false;
		}
		return parent::check();
	}
	
	function store() {
		if (empty($this->ctime)) {
			$now = & JFactory::getDate();
			$this->ctime = $now->toMySQL();
		}
		return parent::store();
	}
}
