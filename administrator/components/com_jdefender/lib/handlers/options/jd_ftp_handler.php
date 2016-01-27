<?php
/**
 * $Id: jd_ftp_handler.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined ('_JEXEC') or die('Restricted Access');

class JD_Ftp_Handler extends JD_Handler
{
	function __construct() {
		parent::__construct();
	}
	
	function handleResults(&$results) {
		if ($results !== false)
			return;
			
		$value['type'] = 'ftp';
		parent::handleLog('options', 'FTP mode disabled', $value, JText::_('Ftp mode is not enabled'), $status = 'disabled');
	}
}