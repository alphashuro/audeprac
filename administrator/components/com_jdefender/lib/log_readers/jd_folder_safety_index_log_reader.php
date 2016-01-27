<?php 
/**
 * $Id: jd_folder_safety_index_log_reader.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined("_JEXEC") or die("Restricted Access");
class JD_Folder_Safety_Index_Log_Reader extends JD_Log_Reader
{
	function getTables() {
		$tables = array();
		
		$data = array();
		$data [] = array('<b>'.JText::_('Directory').'</b>', '<b>'.$this->_logRecord->url.'</b>');
		$data [] = array(JText::_('Issue'), JText::_('Missing index.html file'));
		$data [] = array(JText::_('Description'), JText::_('Missing index.html file may allow viewing directory contents'));
		
		$tables [] = $data;
		
		unset($this->_logRecord->url);
		unset($this->_logRecord->status);
		
		return $tables;
	}
}