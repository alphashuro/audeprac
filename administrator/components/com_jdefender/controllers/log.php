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
defined("_JEXEC") or die("Restricted Access");

jimport('joomla.application.component.controller');

class JDefenderControllerLog extends JController
{
	function __construct()
	{
		parent::__construct();
	}
	
	function display()
	{
		JRequest::setVar('view', 	'log');
		JRequest::setVar('layout', 	'log_groups');
		
		parent::display();
	}
	
	function showOptions() {
		JRequest::setVar('view', 	'log');
		JRequest::setVar('layout', 	'options');
		parent::display();
	}
	
	function close() 
	{
		$this->setRedirect('index.php?option=com_jdefender&controller=log');
	}
	
	function showLog() {
		$types = JRequest::getVar('cid');
		
		JRequest::setVar('layout', 'default');
		JRequest::setVar('types', $types);
		JRequest::setVar('view', 'log');
		
		
		parent::display();
	}
	
	function clearLog() {
		$type 		= JRequest::getVar('type', array());
		$redirect 	= JRequest::getVar('redirect');
		
		if ($redirect) {
			$redirect = base64_decode($redirect);
		}
		else {
			$redirect = 'index.php?option=com_jdefender&controller=log&view=log&layout=log_groups';
		}
		
		$model = & JModel::getInstance('Log', 'JDefenderModel');
		$count = $model->deleteGroups($type);
		
		$message = '';
		if ($count) {
			$message = JText::_('Log Records Deleted').': '.$count;
		}
		
		$this->setRedirect($redirect, $message);
	}

	function remove()
	{
		$post = JRequest::get('post');		
		$ids = $post['cid'];
		
		$model = & $this->getModel('log');
		if($model->delete($ids))
		{
			$message = "The records were deleted successfully";
			$type = 'message';
		} else
		{
			$message = "Error Occured While deleting records";
			$type = 'notice';
		}
		$this->setRedirect( 'index.php?option=com_jdefender', JText::_($message), $type);
	}
	
	function removeGroup() {
		$ids = JRequest::getVar('cid');
		
		$model = & $this->getModel('log');
		if ($model->deleteGroups($ids)) {
			$msg = JText::_('The logs were deleted succesfully');
			$type = 'message';
		}
		else {
			$message = JText::_('Error deleting logs');
			$type = 'notice';
		}
		$this->setRedirect('index.php?option=com_jdefender&controller=log&view=log&layout=log_groups', $message, $type);
	}
	/**
	 * Ignore the log
	 * @return unknown_type
	 */
	function ignore() {
		JRequest::checkToken() or die('Invalid token');
		
		$cid = JRequest::getVar('cid');
		if (empty($cid))
			return;
		
		settype($cid, 'array');
		JArrayHelper::toInteger($cid);
		
		JRequest::setVar('view', 'log');
		JRequest::setVar('layout', 'default');
	}
	
	function getLogGroupOptions() {
		$type = JRequest::getCmd('type');
		
		JRequest::setVar('view', 'log');
		JRequest::setVar('layout', 'options');
		
		parent::display();
	}
	
	
	
	// ***************************** Action handling tasks *****************************
	
	function ignoreLog() {
		global $mainframe;		
		
		list($action, $logRecords) = $this->_getAction();
		$action->addIgnore($logRecords);
		
		if ($action->ignore() !== false) 
		{
			$mainframe->enqueueMessage(JText::_('Operation completed successfully'));
		}
		else 
		{
			$mainframe->enqueueMessage($action->getError(), 'error');
		}
		
		$inIFrame = JRequest::getCmd('tmpl') == 'component';
		
		if ($inIFrame)
		{
			JRequest::setVar('refresh', true);
			JRequest::setVar('deleted', true);
			return $this->getLogGroupOptions();
		}
		else 
		{
			$types 	= JRequest::getVar('types');
			$type 	= $types[ 0 ];
			
			$this->setRedirect('index.php?option=com_jdefender&controller=log&task=showLog&cid[]='.$type);
		}
	}
	
	function acceptLog() {
		global $mainframe;
		
		list($action, $logRecords) = $this->_getAction();
		$action->addAccept($logRecords);
		
		// Fix the issue :)
		$count = $action->accept();
		
		if ($count !== false) {
			$mainframe->enqueueMessage(JText::_('Operation completed successfully'));
		}
		else {
			$mainframe->enqueueMessage($action->getError(), 'error');
		}
		
		$inIFrame = JRequest::getCmd('tmpl') == 'component';
		
		if ($inIFrame)
		{
			JRequest::setVar('refresh', true);
			return $this->getLogGroupOptions();
		}
		else 
		{
			$types 	= JRequest::getVar('types');
			$type 	= $types[ 0 ];
			
			$this->setRedirect('index.php?option=com_jdefender&controller=log&task=showLog&cid[]='.$type);
		}
	}
	
	function fixLog() {
		global $mainframe;
		
		list($action, $logRecords) = $this->_getAction();
		$action->addFix($logRecords);

		// Fix the issue :)
		$count = $action->fix();
		
		if ($count !== false) {
			$mainframe->enqueueMessage(JText::_('Item successfully fixed'));
		}
		
		
		if ($action->getError()) {
			$count = count($action->getErrors());
			
			$msg = array();
			for ($i = 0; $i < $count; $i++) {
				$msg [] = $action->getError($i);
			}
			
			$mainframe->enqueueMessage(implode('<br />', $msg), 'error');
		}
		
		$inIFrame = JRequest::getCmd('tmpl') == 'component';
		
		if ($inIFrame)
		{
			JRequest::setVar('refresh', true);
			return $this->getLogGroupOptions();
		}
		else 
		{
			$types 	= JRequest::getVar('types');
			$type 	= $types[ 0 ];
			
			$this->setRedirect('index.php?option=com_jdefender&controller=log&task=showLog&cid[]='.$type);
		}
	}
	
	function deleteLog() {
		global $mainframe;
		
		list($action, $logRecords) = $this->_getAction();
		$action->addToDelete($logRecords);
		// Fix the issue :)
		$count = $action->delete();
		
		if ($count !== false) {
			$mainframe->enqueueMessage(JText::_('Items deleted').': '.$count);
		}
		else {
			$mainframe->enqueueMessage($action->getError(), 'error');
		}
		
		$inIFrame = JRequest::getCmd('tmpl') == 'component';
		
		if ($inIFrame)
		{
			JRequest::setVar('refresh', true);
			return $this->getLogGroupOptions();
		}
		else 
		{
			$types 	= JRequest::getVar('types');
			$type 	= $types[ 0 ];
			
			$this->setRedirect('index.php?option=com_jdefender&controller=log&task=showLog&cid[]='.$type);
		}
	}
	
	function blockFileLog() {
		global $mainframe;
		
		list($action, $logRecords) = $this->_getAction();
		$action->addBlock($logRecords);
		// Fix the issue :)
		$count = $action->block(true);
		
		if ($count !== false) {
			$mainframe->enqueueMessage(JText::_('Files blocked').': '.$count);
		}
		else {
			$mainframe->enqueueMessage($action->getError(), 'error');
		}
		
		$inIFrame = JRequest::getCmd('tmpl') == 'component';
		
		if ($inIFrame)
		{
			JRequest::setVar('refresh', true);
			return $this->getLogGroupOptions();
		}
		else 
		{
			$types 	= JRequest::getVar('types');
			$type 	= $types[ 0 ];
			
			$this->setRedirect('index.php?option=com_jdefender&controller=log&task=showLog&cid[]='.$type);
		}
	}
	
	function unblockFileLog() {
		global $mainframe;
		
		list($action, $logRecords) = $this->_getAction();
		$action->addBlock($logRecords);
		// Fix the issue :)
		$count = $action->block(false);
		
		if ($count !== false) {
			$mainframe->enqueueMessage(JText::_('Files unblocked').': '.$count);
		}
		else {
			$mainframe->enqueueMessage($action->getError(), 'error');
		}
		
		$inIFrame = JRequest::getCmd('tmpl') == 'component';
		
		if ($inIFrame)
		{
			JRequest::setVar('refresh', true);
			return $this->getLogGroupOptions();
		}
		else 
		{
			$types 	= JRequest::getVar('types');
			$type 	= $types[ 0 ];
			
			$this->setRedirect('index.php?option=com_jdefender&controller=log&task=showLog&cid[]='.$type);
		}
	}
	
	// entodo: completeNtest
	function lockIPLog() {
		global $mainframe;
		
		list($action, $logRecords) = $this->_getAction();
		$action->addBlock($logRecords);
		// Fix the issue :)
		$count = $action->block(true);
		
		if ($count !== false) {
			$mainframe->enqueueMessage(JText::_('Operation completed successfully'));
		}
		else {
			$mainframe->enqueueMessage($action->getError(), 'error');
		}
		
		$inIFrame = JRequest::getCmd('tmpl') == 'component';
		
		if ($inIFrame)
		{
			JRequest::setVar('refresh', true);
			return $this->getLogGroupOptions();
		}
		else 
		{
			$types 	= JRequest::getVar('types');
			$type 	= $types[ 0 ];
			
			$this->setRedirect('index.php?option=com_jdefender&controller=log&task=showLog&cid[]='.$type);
		}
	}
	
	// entodo: completeNtest
	function unlockIPLog() {
		global $mainframe;
		
		list($action, $logRecords) = $this->_getAction();
		$action->addBlock($logRecords);
		// Fix the issue :)
		$count = $action->block(false);
		
		if ($count !== false) {
			$mainframe->enqueueMessage(JText::_('Operation completed successfully'));
		}
		else {
			$mainframe->enqueueMessage($action->getError(), 'error');
		}
		
		$inIFrame = JRequest::getCmd('tmpl') == 'component';
		
		if ($inIFrame)
		{
			JRequest::setVar('refresh', true);
			return $this->getLogGroupOptions();
		}
		else 
		{
			$types 	= JRequest::getVar('types');
			$type 	= $types[ 0 ];
			
			$this->setRedirect('index.php?option=com_jdefender&controller=log&task=showLog&cid[]='.$type);
		}
	}
	
	/**
	 * Shows 'Inform developer' page :)
	 */
	function reportDeveloperLog() {
		JRequest::setVar('view', 'log');
		JRequest::setVar('layout', 'report');

		return parent::display();
	}
	
	function addExceptionLog() {
		global $mainframe;
			
		list($action, $logRecords) = $this->_getAction();
		$action->addException($logRecords);
		
		
		$count = $action->makeException();
		
		if ($count !== false) {
			$mainframe->enqueueMessage(JText::_('Operation completed successfully'));
		}
		else {
			$mainframe->enqueueMessage($action->getError(), 'error');
		}
		
		$inIFrame = JRequest::getCmd('tmpl') == 'component';
		
		if ($inIFrame)
		{
			JRequest::setVar('refresh', true);
			return $this->getLogGroupOptions();
		}
		else 
		{
			$types 	= JRequest::getVar('types');
			$type 	= $types[ 0 ];
			
			$this->setRedirect('index.php?option=com_jdefender&controller=log&task=showLog&cid[]='.$type);
		}
	}
	
	/**
	 * Cleans blocked IP logs
	 */
	function purgeBlockedIpsLog() {
		global $mainframe; 
		
		JRequest::checkToken() or die('Invalid token');
		
		$types 	= JRequest::getVar('types', array(), 'default', 'array');
		$type 	= JRequest::getCmd('type');
		
		if (empty($types))
			$types [] = $type;
		
		$model = & JModel::getInstance('Log', 'JDefenderModel');
		
		$count = $model->deleteBlockedIpLogs($types);
		
		if ($count !== false) {
			$mainframe->enqueueMessage(JText::_('Logs cleaned').': '.$count);
		}
		
		$inIFrame = JRequest::getCmd('tmpl') == 'component';
		
		if ($inIFrame)
		{
			JRequest::setVar('refresh', true);
			return $this->getLogGroupOptions();
		}
		else 
		{	
			$this->setRedirect('index.php?option=com_jdefender&controller=log&task=showLog&cid[]='.$types[ 0 ]);
		}
	}
	
	function fixFolderSafetyAllLog() {
		global $mainframe;
		
		$action = & JD_Action::getInstance('folder_safety_index');
		
		$model = & JModel::getInstance('Log', 'JDefenderModel');
		$model->setState('type', 'folder_safety_index');
		$model->setState('limit', 0);
		$model->setState('limitstart', 0);
		
		$data = $model->getData();
		
		$ids = array();
		
		foreach ($data as $e)
			$ids [] = $e->id;

		$action->addFix($ids);
		
		$count = $action->fix();
		
		$mainframe->enqueueMessage(JText::_('Folders fixed').': '.$count);
		
		$inIFrame = JRequest::getCmd('tmpl') == 'component';
		
		if ($inIFrame)
		{
			JRequest::setVar('refresh', true);
			return $this->getLogGroupOptions();
		}
		else 
		{	
			$this->setRedirect('index.php?option=com_jdefender&controller=log&task=showLog&cid[]=folder_safety_index');
		}
	}
	
	/**
	 * This method can receive both single integer value, and an array
	 */
	function _getAction() {
		global $mainframe;
		
		JRequest::checkToken() or die('Invalid token');
		
		$logId 		= JRequest::getInt('id');
		$logIds		= JRequest::getVar('cid', array(), 'default', 'array');
		
		if (!empty($logIds))
		{
			JArrayHelper::toInteger($logIds);
			$logId = reset($logIds);
		}
		else 
		{
			$logIds = array($logId);
		}
		
		// Check the log
		$logRecord 	= & JTable::getInstance('Log', 'Table');
		if (!$logRecord->load( $logId )) {
			JError::raiseError(404, JText::_('Cannot find log'));
			return false;
		}
		
		$action = & JD_Action::getInstance($logRecord->type);
		
		if (empty($action)) {
			JError::raiseError(404, JText::_('Cannot find action for type').': '.$logRecord->type);
			return array(false, false);
		}
		
		return array(&$action, $logIds);
	}
	
	function support()
	{
		$doc = &JFactory::getDocument();
		$js = 'window.open("http://support.mightyextensions.com/en/mighty-defender-joomla-security-firewall-component.html")';
		$doc->addScriptDeclaration($js);
		JRequest::setVar( 'view'  , 'log');
		parent::display();
	}
}
