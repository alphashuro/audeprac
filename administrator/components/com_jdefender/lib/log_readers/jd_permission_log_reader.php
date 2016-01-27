<?php
/**
 * $Id: jd_permission_log_reader.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined ('_JEXEC') or die('Restricted Access');


class JD_Permission_Log_Reader extends JD_Log_Reader
{
	function __construct() {
		parent::__construct();
	}
	
	function getTables() {
		$current 	= $this->readCurrentState($this->_filesystemState);
		$actual 	= $this->read($this->_logRecord);
		
		$model 	= & JModel::getInstance('Configuration', 'JDefenderModel');
		$params	= new JParameter($model->getIni());
		
		$isFile = is_file($this->_logRecord->url);
		$dirPermission 	= $params->get('permission_max_dir_permission', '755');
		$filePermission = $params->get('permission_max_file_permission', '644');
		
		$data = array();
		$data [] = array('<b>'.JText::_('Current').'</b>', '<b>'.JText::_('Last Scan').'</b>', '<b>'.JText::_('Configuration').'</b>');
		$data [] = array($actual['permissions'], $current['permissions'], $isFile ? $filePermission : $dirPermission);
		
		unset($this->_logRecord->url);
		
		return array('' => $data);
	}
	
	function read($logRecord) {
		if (empty($logRecord) || empty($logRecord->post))	
			return null;

		if (!is_array($logRecord->post))
			$data = @unserialize($logRecord->post);
		else
			$data = $logRecord->post;
		 
		if (!is_array($data))
			return null;
			
		$permission = fileperms($logRecord->url) & 0777;
		
		if (!is_null($permission))
			$data['permissions'] = sprintf('%o', (int)$permission);
		
		return $data;
	}
	
	function readCurrentState($state) {
		$data = array();
		$data['permissions'] = sprintf('%o', (int)$state->permission);
		
		return $data;
	}
}