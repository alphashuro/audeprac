<?php 
/**
 * $Id: jd_file_integrity_php_bad_functions_log_reader.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined("_JEXEC") or die("Restricted Access");

class JD_File_Integrity_Php_Bad_Functions_Log_Reader extends JD_Log_Reader
{
	function __construct() {
		parent::__construct();
	}
	
	function getTables() {
		$rows = $this->read($this->_logRecord);
		
		return array('Functions that can <i>possibly</i> cause security flaws if not used properly' => $rows);
	}
	
	function read($logRecord = null) {
		$file = $logRecord->url;
		
		unset($logRecord->url);
		unset($logRecord->status);
		
		
		if (empty($logRecord->issue))
			return null;
		
		$issue = explode("\n", trim($logRecord->issue, "\r\n"));
		$issue = str_replace(array("\r", "\n"), '', $issue);
		
		$data = array();
		$data [] = array('<b>'.JText::_('File').'</b> ', '<b>'.$file.'</b>');
		
		$i = 1;
		foreach ($issue as $item) {
			$data [] = array(JText::_('Bad function').' '.JText::_('#').$i, $item);
			$i++;
		}	
		
		return $data;
	}
}