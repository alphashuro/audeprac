<?php
/**
 * $Id: jd_options_log_reader.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined("_JEXEC") or die("Restricted Access");
class JD_Options_Log_Reader extends JD_Log_Reader
{
	function getTables() {
		$tables = array();
		$actual = $this->read();
	
		$data = array();
		
		switch ($actual['type']) {
			case 'ftp':
				$data [] = array('<b>'.JText::_('Issue').'</b>', '<b>'.JText::_('FTP mode is disabled').'</b>');
				$data [] = array(JText::_('Description'), JText::_('Using FTP mode is recommended'));
				
				$tables [] = $data;
				break;
			case 'admins':
				$data [] = array('<b>'.JText::_('Issue').'</b>', '<b>'.JText::_('Guessable usernames are used for Administrator accounts').'</b>');
				$data [] = array(JText::_('Description'), JText::_('For safety reasons it is not recommended to use guessable usernames for administrator accounts'));
				
				$tables [] = $data;
				
				$data = array();
				
				$data [] = array('<b>'.JText::_('Username').'</b>', '<b>'.JText::_('User ID').'</b>');
				$admins = $actual['value'];
				foreach ($admins as $admin) {
					list($id, $username) = explode(' - ', $admin);
					
					$data [] = array($username, $id);
				}
				
				$tables ['Guessable usernames'] = $data;
				break;
				
			case 'joomlaversion':
				$data [] = array('<b>'.JText::_('Issue').'</b>', '<b>'.JText::_('Your joomla installation is outdated').'</b>');
				
				if (isset($actual['value'][0], $actual['value'][1]))
					$data [] = array(JText::_('Description'), 'Joomla! '.$actual['value'][1].' '.JText::_('is available').'. '.JText::_('Your version is').': '.$actual['value'][0]);
				$tables [] = $data;
		}
		
		
		
		
		unset($this->_logRecord->url);
		unset($this->_logRecord->status);
		
		return $tables;
	}
}