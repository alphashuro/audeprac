<?php
/**
 * $Id: jd_flood_log_reader.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined ('_JEXEC') or die('Restricted Access');

class JD_Flood_Log_Reader extends JD_Abstract_Injection_Log_Reader
{
	function __construct() {
		parent::__construct();
	}
	
	
	function read($logRecord) {
		$logRecord->issue = false;
		
		return parent::read($logRecord);
	}
}