<?php
/**
 * $Id: jd_file_integrity_php_jexec_log_reader.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined ('_JEXEC') or die('Access Restricted');


class JD_File_Integrity_Php_Jexec_Log_Reader extends JD_Log_Reader
{
	function getTables() {
		$tables = array();

		$data = array();
		
		$data [] = array('<b>'.JText::_('File').'</b>', '<b>'.$this->_logRecord->url.'</b>');
		$data [] = array(JText::_('Issue'), JText::_('Missing _JEXEC constant check'));
		$data [] = array(JText::_('Description'), JText::_('Files without _JEXEC constant check can be executed by pointing to them from browser'));
		
		$tables [] = $data;
		
		unset($this->_logRecord->url);
		unset($this->_logRecord->status);
		
		return $tables;
	}
}