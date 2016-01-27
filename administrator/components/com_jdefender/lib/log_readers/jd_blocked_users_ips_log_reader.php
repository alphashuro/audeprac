<?php
/**
 * $Id: jd_blocked_users_ips_log_reader.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined ('_JEXEC') or die ('Access Restricted');


class JD_Blocked_Users_Ips_Log_Reader extends JD_Log_Reader
{
	function __construct() {
		parent::__construct();
	}
	
	function getTables() {
		$tables = array();
		
		$data = array();
		
		$data [] = array('<b>'.JText::_('URL').'</b>', $this->_logRecord->url);
		
		if ($this->_logRecord->ip)
		{	
			$ipBlocked = JD_Block_Helper::isIPBlocked($this->_logRecord->ip);
			
			$src = $ipBlocked ? JURI::base().'components/com_jdefender/images/locked.gif' : JURI::base().'components/com_jdefender/images/unlocked.gif';
			
			$img = JHTML::image(
				$src, 
				JText::_('Block/Unblock user'), 
				array(
					'id' => 'user_ip_img', 
					'onclick' => 'toggleLockImage(); xajax_jdBlockParam(\'user_ip_img\', \'ip\', \''.$this->_logRecord->ip.'\')',
					'title' => JText::_('Block/Unblock IP'),
					'class' => 'hasTip',
					'style' => 'cursor: pointer'
				)
			);
			
			$data [] = array('<b>'.JText::_('IP Address').'</b>', $img.' ' .$this->_logRecord->ip);
			
		}
		
		if ($this->_logRecord->user_id)
		{
			$user = & JFactory::getUser($this->_logRecord->user_id);
			
			$isUserBlocked = JD_Block_Helper::isUserBlocked($user->get('id'));
			
			$src = $isUserBlocked ? JURI::base().'components/com_jdefender/images/locked.gif' : JURI::base().'components/com_jdefender/images/unlocked.gif';
			
			$img = JHTML::image(
				$src, 
				JText::_('Block/Unblock user'), 
				array(
					'id' => 'user_img_'.$user->get('id'), 
					'onclick' => 'toggleLockImage(); xajax_jdBlockParam(\'user_img_'.$user->get('id').'\', \'user\','.$user->get('id').')',
					'title' => JText::_('Block/Unblock user'),
					'class' => 'hasTip',
					'style' => 'cursor: pointer'
				)
			);
			
			
			$userId = $img.' '.$user->get('id');
			
			$data [] = array('<b>'.JText::_('User ID').'</b>', $userId);
			$data [] = array('<b>'.JText::_('Username').'</b>', $user->get('username'));
			$data [] = array('<b>'.JText::_('Name').'</b>', $user->get('name'));
			$data [] = array('<b>'.JText::_('Email').'</b>', JHTML::_('email.cloak', $user->get('email')));
		}
		
		// unset($this->_logRecord->status);
		unset($this->_logRecord->url);
		
		$tables [] = $data;
		return $tables;
	}
}