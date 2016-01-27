<?php
/**
 * $Id: jd_admins_handler.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined ('_JEXEC') or die('Restricted Access');

class JD_Admins_Handler extends JD_Handler
{
	function __construct() {
		parent::__construct();
	}
	
	function handleResults(&$results) {
		if (empty($results) || !is_array($results))
			return;
			
		$admins = array();

		foreach ($results as $admin) {
			$admins [] = $admin->id.' - '.$admin->username;
		}
		
		$issue 			= JText::_('Default admin usernames are being used').': '.implode(' | ', $admins);
		$value['type'] 	= 'admins';
		$value['value']	= $admins;
		
		parent::handleLog('options', 'Default admin usernames', $value, $issue, $status = 'default usernames in use');
	}
}