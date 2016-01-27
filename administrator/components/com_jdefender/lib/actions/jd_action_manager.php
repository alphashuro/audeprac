<?php
/**
 * $Id: jd_action_manager.php 7770 2012-01-26 12:39:15Z kostya $
 * $LastChangedDate: 2012-01-26 18:39:15 +0600 (Thu, 26 Jan 2012) $
 * $LastChangedBy: kostya $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined ('_JEXEC') or die("Restricted Access");

/**
 * Class for managing actions, i.e. - Fix files, permsissions, missing php_jexec 
 * @author nurlan
 * 
 * // singletone
 */
class JD_Action_Manager extends JObject
{
	var $_actions = null;
	var $_component_params = null;
	
	function __construct() {
		parent::__construct();
		
		$this->_actions = array();
	}
	
	/**
	 * 
	 * @return JD_Action_Manager Action manager
	 */
	function & getInstance() {
		static $instance = null;
		
		if (empty($instance)) 
		{
			$instance = new JD_Action_Manager();
		}
		
		return $instance;
	}
	
	/**
	 * Add action from log entry, and return modified log record
	 * @param stdclass $logRecord
	 * @return stdclass
	 */
	function addActionFromLog($log) {
		$logRecord = clone($log); 
		$params = $this->getComponentParams();
		
		$this->_writeLog('Log record: '.$logRecord->url.' - '.$logRecord->type, 'info');
		
		// Select the task
		$task = false;
		
		// File integrity check
		if (in_array($logRecord->type, array('file_integrity_new', 'file_integrity_changed')))
		{
			$task = $params->get('file_integrity_on_bad_file');
		}

		// entodo: defender cannot fix the php_bad functions, so, need to put check here ;_
		if (in_array($logRecord->type, array('file_integrity_php_bad_functions', 'file_integrity_php_jexec')))
		{
			$task = $params->get('php_on_bad_file');
			
			if ($logRecord->type == 'file_integrity_php_bad_functions' && $task = 'fix')
				return $logRecord;
		}
		
		if (empty($task))
			$task = $params->get(strtolower($logRecord->type).'_on_bad_file');
		
		// If we have an automatic task for this type of issue, add it
		if ($task)
		{
			// Add the action
			if ($this->addAction($logRecord->type, $task, $logRecord->url, true))
			{
				// Set the log status 
				if (in_array(substr($task, -1), array('euioa')))
					$logRecord->status = $task.'d';
				else
					$logRecord->status = $task.'ed';
					
				$this->_writeLog($logRecord->type.' - '.$task.' - '.$logRecord->url, $logRecord->status);
			}
			else {
				$this->_writeLog($this->getError(), 'error');
			}
		}
		
		
		return $logRecord;
	}
	
	/**
	 * Add an action to be performed 
	 * @param $actionType
	 * @param $task
	 * @param $item
	 * @param $performAction boolean Perform the action
	 * @return boolean
	 */
	function addAction($actionType, $task, $item, $performAction = false) {
		if (empty($item))
			return false;
		
		$method = false;
		switch ($task) {
			case "none":
				return false;
				
			case "block":
			case "quarantine":
			case "fix":
				$method = 'add'.ucfirst($task);
				break;
		}
		
		if (!$method) {
			$this->setError(JText::_('Method not found').': add'.ucfirst($task));
			return false;
		}
		else {
			$action = & $this->_getAction($actionType);
			if (empty($action)) {
				$this->setError(JText::_('Cannot find action, type'). ': '.$actionType);
				return false;
			}
			$action->$method($item);
			
			if ($performAction) {
				$res = $action->performActions();
				 
				// check for an error
				if (in_array(false, $res)) {
					$this->setError($action->getError());
					return false;
				}
			}
		}
		
		return true;
	}
	
	/**
	 * Perform the added actions
	 * @return unknown_type
	 */
	function performActions() {
		foreach ($this->_actions as $type => $action)
		{
			 $res = $action->performActions();
			 
			 // check for an error
			 if (in_array(false, $res)) {
			 	$this->setError($action->getError());
			 	return false;
			 }
		}
		return true;
	}
	
	/**
	 * Factory method: Get the action
	 * @param string $type
	 * @return JD_Action the action
	 */
	function & _getAction($type) {
		$action = & JD_Action::getInstance($type);
		
		return $action;
	}
	
	/**
	 * Get the component params
	 * @return JParameter
	 */
	function getComponentParams($forceReload = false) {
		if (empty($this->_component_params) || $forceReload)
		{
			$model = & JModel::getInstance('Configuration', 'JDefenderModel');
			$this->_component_params 	= new JParameter($model->getIni());
		}
		
		return $this->_component_params;
	}
	
	/**
	 * Write debug information
	 * @param $str
	 * @param $comment
	 */
	function _writeLog($str, $comment) 
	{
		return false;
		
		$logger = & JD_Logger::getInstance(__CLASS__);
		$logger->log($str, $comment);
	}
}