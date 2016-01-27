<?php
/**
 * $Id: jd_error.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined ('_JEXEC') or die("Restricted Access");

/**
 * Unset default Joomla error handlers
 * @author nurlan
 *
 */
class JD_Error
{
	/**
	 * Unset error handlers, that cause error page
	 */
	function unsetErrorHandlers()
	{
		if (!empty($GLOBALS['_JERROR_HANDLERS_BACKUP_DEFENDER']))
			return;
		
			
		$GLOBALS['_JERROR_HANDLERS_BACKUP_DEFENDER'] = array();

		/// backup the error handlers
		foreach ($GLOBALS['_JERROR_HANDLERS'] as $k => $v)
		{
			$errorHandler = $GLOBALS['_JERROR_HANDLERS'][ $k ];
			$GLOBALS['_JERROR_HANDLERS_BACKUP_DEFENDER'][ $k ] 	= $errorHandler;
			
			// ignore all errors
			$GLOBALS['_JERROR_HANDLERS'][ $k ]	= array("mode" => "ignore");
		}
	}
	
	/**
	 * Unset error handlers, that cause error page
	 */
	function putErrorHandlersBack()
	{
		
		if (empty($GLOBALS['_JERROR_HANDLERS_BACKUP_DEFENDER']))
			return;
		
		/// backup the error handlers
		foreach ($GLOBALS['_JERROR_HANDLERS'] as $k => $v)
		{
			$GLOBALS['_JERROR_HANDLERS'][ $k ] = $GLOBALS['_JERROR_HANDLERS_BACKUP_DEFENDER'][ $k ];
		}
		
		
		// unset the backup
		unset($GLOBALS['_JERROR_HANDLERS_BACKUP_DEFENDER']);
	}
}