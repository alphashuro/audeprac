<?php
/**
 * $Id: jd_file_integrity_handler.php 7311 2011-08-19 11:18:02Z shitz $
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
class JD_File_Integrity_Handler extends JD_Handler
{	
	function __construct() {
		parent::__construct();
		
		jimport ('joomla.filesystem.file');
		jimport ('joomla.filesystem.folder');
	}
	
	function handleResults(&$data)
	{
		if (empty($data) || !is_array($data) || !is_array($data[ 1 ]))
			return;
		
		foreach ($data[ 1 ] as $entry) {
			$newData = array();
			foreach ($entry->mismatch as $k => $m) {
				$newData[ $k ] = $m[ 0 ];
			}
			
			// We have new file
			if ($entry->reason == 'new')
			{	
				parent::handleLog('file_integrity_new', $entry->file, $newData, 'New file', 'new');
			}
			// We have file changed.
			else if ($entry->reason == 'changed') {
				// Get the new filename
				$status = 'changed';
				
				$issue = array_keys($newData);
				foreach ($issue as $k => $v) {
					$issue [ $k ] = JText::_( $v );
				}
					 
				parent::handleLog('file_integrity_changed', $entry->file, $newData, implode(', ', $issue), $status);
			}
		}
	}
}