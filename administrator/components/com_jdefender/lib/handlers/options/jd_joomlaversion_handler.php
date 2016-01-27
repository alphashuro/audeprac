<?php
/**
 * $Id: jd_joomlaversion_handler.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined ('_JEXEC') or die('Restricted Access');

class JD_Joomlaversion_Handler extends JD_Handler
{
	function __construct() {
		parent::__construct();
	}
	
	function handleResults(&$results) {
		if (empty($results) || !is_array($results))
			return;

		if ($results[0] && $results[ 1 ] && version_compare($results[0], $results[ 1 ], '<'))
		{
			$issue 			= JText::_('Joomla update available').': '.@$results[1];
			$value['type'] 	= 'joomlaversion';
			$value['value']	= $results;
			
			parent::handleLog('options', 'Joomla outdated', $value, $issue, $status = 'joomla outdated');
		}
	}
}
