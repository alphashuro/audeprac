<?php
/**
 * $Id: jd_abstract_injection_log_reader.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined ('_JEXEC') or die('Restricted Access');

/**
 * Abstract class that is capable of reading the injection logs.
 * @author nurlan
 *
 */
class JD_Abstract_Injection_Log_Reader extends JD_Log_Reader
{
	function __construct() {
		parent::__construct();
	}
	
	function getTables() {
		return $this->read($this->_logRecord);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see components/com_jdefender/lib/log_readers/JD_Log_Reader#read($logRecord)
	 */
	function read($logRecord) {
		if (empty($logRecord->url))
			return null;
		
		$tables = array();
		
		// issue
		if ($logRecord->issue) {
			$parts = explode('=', $logRecord->issue, 2);
		
			$varName = @$parts[ 0 ];
			$value = @$parts[ 1 ];
			
			$tables['Injection'] = array(
				array('<b>'.JText::_('Variable').'</b>', htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' )),
				array('<b>'.JText::_('Value').'</b>', htmlspecialchars( $varName, ENT_QUOTES, 'UTF-8' ))
			);
			
			// Extension
			if ($logRecord->extension) {
				$parts = explode('::', $logRecord->extension);
				$ext = end($parts);
				
				$tables['Injection'][] = array('<b>'.JText::_('Extension').'</b>', htmlspecialchars($ext, ENT_QUOTES, 'UTF-8'));
			}
		}
		
		// Post
		$post = array();
		if (!is_array($logRecord->post))
			$post = @unserialize($logRecord->post);
		
		$additionalOpts = false;
		if (isset($post['__mighty_defender_info']))
			$additionalOpts = $post['__mighty_defender_info'];
		// The rule id
		if (!empty($additionalOpts['ruleId'])) 
		{	
			$data = array();
			$data [] = array(
				'<b>'.JText::_('Rule').'</b>', 
				JHTML::link('index.php?option=com_jdefender&view=rules&layout=form&id='.(int)$additionalOpts['ruleId'], JText::_('View rule'))
			);
			
			$tables['Rule'] = $data;
		}
		
		// Username
		$userId = (int)$logRecord->user_id;
		$user 	= & JFactory::getUser($userId);
		
		$data = array();
		
		$data [] = array(JText::_('URL'), '<b>'.$logRecord->url.'</b>');
		if ($username = $user->get('username')) {
			$data[] = array(JText::_('User'), '<b>'.htmlspecialchars( $username, ENT_QUOTES, 'UTF-8' ).'</b>');
		}
			
		if ($ip = $logRecord->ip) {
			$data[] = array(JText::_('IP Address'), '<b>'.$ip.'</b>');
		}
	
		// Referer
		if ($referer = $logRecord->referer) {
			$data[] = array(JText::_('Referer'), htmlspecialchars( $referer, ENT_QUOTES, 'UTF-8' ));
		}
		
		// User-agent
		if ($userAgent = $logRecord->user_agent) {
			$data[] = array(JText::_('User-Agent'), htmlspecialchars( $userAgent, ENT_QUOTES, 'UTF-8' ));
		}
		
		$tables['info'] = $data;
		
		
		
		$data = array();
		// Get
		$uri 	= & JURI::getInstance($logRecord->url);
		$get	= $uri->getQuery(true);
		
		if (!empty($get) && is_array($get)) {
			foreach ($get as $k => $v) {
				if (empty($k) || !trim($k))
					continue;
				
				$k = htmlspecialchars( $k, ENT_QUOTES, 'UTF-8' );
				$v = htmlspecialchars( $v, ENT_QUOTES, 'UTF-8' );
				
				$data[] = array('<b>'.$k.'</b>', $v);
			}
		}
		
		$tables['Get Variables'] = $data;
		
		
		
		// Post
		$data = array();
		
		if (!empty($post) && is_array($post)) {
			$data['-- Post Variables --'] = ' ';
			foreach ($post as $k => $v) {
				if (empty($k) || !trim($k))
					continue;
					
				$k = htmlspecialchars( $k, ENT_QUOTES, 'UTF-8' );
				$v = htmlspecialchars( $v, ENT_QUOTES, 'UTF-8' );
				
				$data[] = array('<b>'.$k.'</b>', $v);
			}
		}
		
		$tables['Post Variables'] = $data;

		// Cookies
		$data = array();
		
		$cookie = @unserialize($logRecord->cook);
		if (!empty($cookie) && is_array($cookie)) {
			foreach ($cookie as $k => $v) {
				if (empty($k) || !trim($k))
					continue;
				
				$k = htmlspecialchars( $k, ENT_QUOTES, 'UTF-8' );
				
				if (is_array($v)) 
					$v = JArrayHelper::toString($v);
				
				$v = htmlspecialchars( $v, ENT_QUOTES, 'UTF-8' );
				
				$data[] = array('<b>'.$k.'</b>', $v);
			}
		}
		
		$tables['Cookies'] = $data;
		
		unset($logRecord->url);
				
		return $tables;
	}
}