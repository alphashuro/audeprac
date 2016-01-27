<?php
/**
 * $Id: jd_permission_handler.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined ('_JEXEC') or die();


/**
 * Class that handles the results of the filesystem scan.
 * @author Nurlan
 *
 */
class JD_Permission_Handler extends JD_Handler
{	
	function __construct() {
		parent::__construct();
		
		jimport ('joomla.filesystem.file');
		jimport ('joomla.filesystem.folder');
	}
	
	function handleResults(&$data) 
	{
		if (empty($data) || !is_array($data))
			return;

		// proccess directories
		if (is_array($data[ 1 ])) 
		{
			foreach ($data[ 1 ] as $entry) {
				$newData = array();
				
				$target = $entry->dir;
				$status = 'bad_permissions';
				$issue 	= $entry->permission;
				
				$newData['permissions'] = $entry->permission;
				
				parent::handleLog('permission', $target, $newData, decoct($issue), $status);
			}
		}
		
		// Process files
		if (is_array($data[ 2 ])) 
		{
			foreach ($data[ 2 ] as $entry) {
				$newData = array();
				
				$target = $entry->file;
				$status = 'bad_permissions';
				$issue 	= $entry->permission;
				
				$newData['permissions'] = $entry->permission;
				
				parent::handleLog('permission', $target, $newData, decoct($issue), $status);
			}
		}
	}
}