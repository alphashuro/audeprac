<?php
/**
 * $Id: jd_live_protection_action.php 7311 2011-08-19 11:18:02Z shitz $
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
 * Abstract parent class for all Live Protection actions.
 * Defines "stop"  method
 * @author nurlan
 *
 */
class JD_Live_Protection_Action extends JD_Action
{
	function __construct() {
		parent::__construct ();
	}
	
	/**
	 * Blocks IP Address
	 * @override
	 * @param $blockReason
	 * @param $block
	 */
	function block($blockReason, $block = true) 
	{
		if (empty($this->_blocks) && ! count($this->_blocks))
			return false;
		
		
		require_once JPATH_ROOT . DS . 'administrator' . DS . 'components' . DS . 'com_jdefender' . DS . 'helpers' . DS . 'block.php';

		$ids = array ();
		
		foreach ( $this->_blocks as $k => $item ) {
			if (is_numeric ( $item ))
				$ids [] = $item;
		}
		
		$logModel = & JModel::getInstance('Log', 'JDefenderModel');
		$logModel->setState('id', $ids);
		
		$records = $logModel->getData ();
		if (empty ( $records ))
			$records = array();
			
		// Make rules
		foreach ( $records as $rec ) {
			$ip = $rec->ip;
			$type = 'ip';
			
			if ($block)
				JD_Block_Helper::block ( $type, $ip, $blockReason );
			else
				JD_Block_Helper::unblock ( $type, $ip );
		}
		
		$this->_blocks = array ();
		
		if ($block)
			parent::setLogStatus ( $ids, 'blocked' );
		else
			parent::setLogStatus ( $ids, 'unblocked' );
		
		return true;
	}
	
	/**
	 * Assumes that log records 'issue' field contains key=value that can be used for creating rules.
	 * @return int number of succesfully added exceptions
	 */
	function makeException() {
		$ids = array ();
		
		foreach ($this->_exceptions as $k => $item) {
			if (is_numeric($item))
				$ids[] = $item;
		}
		
		$logModel = & JModel::getInstance('Log', 'JDefenderModel');
		$logModel->setState('id', $ids);
		
		$records = $logModel->getData ();
		if (empty ( $records ))
			$records = array();
		
		// Add exception rules
		$row = & JTable::getInstance('Rule', 'Table');
		$fixedIds = array();
		
		foreach ($records as $rec) {
			$vars 		= explode("=", $rec->issue);
			$extension 	= explode('::', $rec->extension);
			
			if (2 != count($vars))
				continue;
			
			$row->id 		= null;
			$row->rule 		= $vars[0]; // The key
			$row->family 	= $log->type;
			$row->type 		= 'none';
			$row->action 	= 'ignore';
			$row->published = 1;
			$row->component = (@$extension[ 0 ] == 'component') ? @$extension[ 2 ] : '*';
			
			if ($row->check() && $row->store())
				$fixedIds [] = $rec->id;
			else
				$this->setError($row->getError());
		}
		
		
		parent::setLogStatus($fixedIds, 'added_to_exceptions');
		
		$this->_exceptions = array();
		
		return count($fixedIds);
	}
}