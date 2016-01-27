<?php
/**
 * $Id: controller.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined('_JEXEC') or die('Restricted Access');

jimport ( 'joomla.application.component.controller' );


class JDefenderController extends JController {
	function __construct() {
		parent::__construct();
	}
	
	
	function display() {
		parent::display ();
	}
	
	
	function cancel() {
		$url = 'index.php?option=com_jdefender&controller=log';
		
		$where = JRequest::getCmd('where');
		
		if ($where == 'report') {
			$logType = JRequest::getCmd('logType');
			
			if ($logType)
				$url = 'index.php?option=com_jdefender&controller=log&task=showLog&cid[]='.$logType;
		}
		
		$this->setRedirect($url);
	}
	
	
	function sendMail() {
		JRequest::checkToken() or die('Invalid token');
		
		$post = JRequest::get('post', JREQUEST_ALLOWRAW);
		
		$bodies 	= $post['body'];
		$subject 	= $post['subject'];
		$addresses 	= $post['email'];
		
		jimport ('joomla.mail.helper');
		
		// Validate the mail
		$i = 0;
		$halt = false;
		$logType 	= JRequest::getCmd('logType');
		$extension 	= JRequest::getString('extension');
		$logId 		= JRequest::getInt('id');
		$iframe		= JRequest::getInt('iframe');
		
		foreach ($addresses as $email) {
			if (!JMailHelper::isEmailAddress($email)) {
				JError::raiseWarning(404, JText::_('Incorrect email address').': '.$email);
				$halt = true;
			}
			
			if (empty($subject[ $i ])) {
				JError::raiseWarning(404, JText::_('Incorrect email address').': '.$email);
				$halt = true;
			}
			
			if (empty($bodies[ $i ])) {
				JError::raiseWarning(404, JText::_('Message is empty').': '.$email);
				$halt = true;
			}
			
			if ($halt) {
				$url = 'index.php?option=com_jdefender&controller=log&task=reportDeveloperLog&filter_extension='.$extension.'&types[]='.$logType;
				if ($iframe)
					$url .= '&tmpl=component';
					
				$this->setRedirect($url);
				
				return;
			}
			
			$i++;
		}
		
		$config = & JFactory::getConfig();
		$sender = array($config->getValue('config.mailfrom'), $config->getValue('config.fromname'));
		
		$i = 0;
		foreach ($addresses as $email) {
			$mail = & JFactory::getMailer();
			$mail->addRecipient($email);
				
			$mail->setSender($sender);
			$mail->addReplyTo($sender);
			
			if ($_SERVER['REMOTE_ADDR'] != '127.0.0.1' && $_SERVER['REMOTE_ADDR'] != '::1')
				$mail->addBCC('defender@mightyextensions.com');
			
			$mail->isHTML(true);
			
			$mail->setSubject($subject[ $i ]);
			$mail->setBody($bodies[ $i ]);
			
			if (!$mail->Send())	{
				$this->setError(JText::_('Failed sending mail'));
				return false;
			}
			
			$i++;
		}
		
		
		$ids = JRequest::getString('ids');
		$ids = explode(',', $ids);
		JArrayHelper::toInteger($ids);
		
		
		$model = & JModel::getInstance('Log', 'JDefenderModel');
		$model->setLogStatus($ids, 'reported_to_developer');
		
		
		if ($iframe)
			$url = 'index.php?option=com_jdefender&tmpl=component&task=showOptions&id='.$logId.'&refresh=1';
		else {
			if ($logType)
				$url = 'index.php?option=com_jdefender&controller=log&task=showLog&cid[]='.$logType;
			else
				$url = 'index.php?option=com_jdefender&controller=log';
		}
		
		$this->setRedirect($url);
	}
}
