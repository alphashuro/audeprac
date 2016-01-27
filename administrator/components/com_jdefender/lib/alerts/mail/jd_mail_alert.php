<?php
/**
 * $Id: jd_mail_alert.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined ('_JEXEC') or die ('Access Restricted');


class JD_Mail_Alert extends JD_Alert
{
	var $_emails;
	
	/**
	 * @override
	 */
	function & getInstance()
	{
		static $instance = null;
		
		if (empty($instance))
		{
			$instance = new JD_Mail_Alert();
		}
		
		return $instance;
	}
	
	
	function send($message)
	{
		global $mainframe;
		
		if (empty($message)) {
			$this->setError(JText::_('The alert is empty'));
			return false;
		}
		
		if (is_array($message))
			$message = implode('<br /><br />', $message);
		
		$recepients = $this->_getEmailsToSend();
		
		if (empty($recepients) || !count($recepients)) {
			$this->setError(JText::_('No recepients for email or bad emails'));
		}	
		
		
		$mail = & JFactory::getMailer();
		$mail->addRecipient($recepients);
		
		$sender = $this->_getSender();

		$mail->setSender($sender);
		$mail->addReplyTo($sender);
		
		$mail->isHTML(true);
		
		$params = & $this->_getParams();
		$subject = $params->get('alerts_mail_subject', 'Mighty Defender alert');
		
		$mail->setSubject($subject);
		$mail->setBody($message);
		

		if (!$mail->Send())
		{
			$this->setError(JText::_('Failed sending mail'));
			return false;
		}
		
		return true;
	}
	
	function _getSender() {
		$config = & JFactory::getConfig();
		
		return array(
			$config->getValue('config.mailfrom'),
			$config->getValue('config.fromname')
		);
	}
	
	
	function _getEmailsToSend() {
		if ( empty($this->_emails))
		{
			jimport ('joomla.mail.helper');
			
			$params = & $this->_getParams();
			
			$emails = trim($params->get('alerts_mail_destination'), ", \r\n");
			$emails = explode(',', $emails);
			
			$validEmails = array();
			
			foreach ($emails as $k => $v) {
				$v = trim($v, ", \r\n");
				if (JMailHelper::isEmailAddress($v))
					$validEmails [] = $v;
			}
			
			$this->_emails = $validEmails;
		}
		return $this->_emails;
	}
}