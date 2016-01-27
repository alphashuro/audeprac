<?php
/**
 * $Id: block.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined("_JEXEC") or die("Restricted Access");
jimport ( 'joomla.application.component.controller' );

class JDefenderControllerBlock extends JController {
	function __construct() {
		parent::__construct ();
	}
	
	function display() {
		JRequest::setVar ( 'view', 'block' );
		parent::display ();
	
	}
	
	function remove() {
		$post 	= JRequest::get ( 'post' );
		$ids 	= $post ['cid'];
		
		
		$model = & $this->getModel ( 'block' );
		if ($model->delete ( $ids )) {
			$message = "The records were deleted successfuly";
			$type = 'message';
		} else {
			$message = "Error Occured While deleting records";
			$type = 'notice';
		}
		$this->setRedirect ( 'index.php?option=com_jdefender&controller=block', JText::_ ( $message ), $type );
	
	}
	
	function add() {
		JRequest::setVar ( 'view', 'block' );
		JRequest::setVar ( 'layout', 'add' );
		
		parent::display ();
	}
	
	function cancel() {
		$this->setRedirect ( 'index.php?option=com_jdefender&controller=block' );
	}
	
	function save() {
		global $mainframe;
		
		$model 	= JModel::getInstance('Block', 'JDefenderModel');
		$vars 	= JRequest::getVar ( 'var', array () );
		
		if ( $model->save($vars) )
		{
			$this->setRedirect('index.php?option=com_jdefender&controller=block', JText::_('Items succesfully added'));
		}
		else {
			JRequest::setVar('view', 'block');
			JRequest::setVar('layout', 'add');

			$mainframe->enqueueMessage($model->getError(), 'error');
			
			parent::display();
		}
	}
	
	function unpublish() {
		return $this->publish(false);
	}

	function publish($publish = true) {
		$ids = JRequest::getVar('cid', array(), 'default', 'array'); 
		
		if (!empty($ids)) {
			$model = & JModel::getInstance('Block', 'JDefenderModel');
			$model->publish($ids, $publish);
		}
		
		$this->setRedirect('index.php?option=com_jdefender&controller=block');
	}
}
