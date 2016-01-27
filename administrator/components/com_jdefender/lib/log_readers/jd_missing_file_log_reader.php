<?php
/**
 * $Id: jd_missing_file_log_reader.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined ('_JEXEC') or die('Restricted Access');

require_once JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_jdefender'.DS.'lib'.DS.'log_readers'.DS.'jd_file_integrity_changed_log_reader.php';


class JD_Missing_File_Log_Reader extends JD_File_Integrity_Changed_Log_Reader
{
	function __construct() {
		parent::__construct();
	}

	function getTables() {
		return parent::getTables();
	} 

	function read($logRecord) {
		return $titles = array(
			"filename" => ' - ',
	        "fullpath" => ' - ',
		    "permission" => ' - ',
		    "ctime" => ' - ',
	    	"mtime" => ' - ',
	    	"size" => ' - ',
	    	"type" => ' - ',
	    	"user" => ' - ',
	    	"group" => ' - ',
	    	"hash_md" => ' - ',
	    	'scandate' => ' - '
		);
	}
}