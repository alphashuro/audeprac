<?php
/**
 * $Id: jd_php_handler.php 7311 2011-08-19 11:18:02Z shitz $
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
class JD_Php_Handler extends JD_Handler
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
		
		// Check JEXEC warnings
		if (is_array($data[ 1 ])) 
		{
			foreach ($data[ 1 ] as $file) {
				$newData = array();
				$status = 'insecure';
				
				parent::handleLog('file_integrity_php_jexec', $file, $newData, JText::_('No _JEXEC check'), $status);
			}
		}
		
		// Check For Bad Function entries
		if (is_array($data[ 2 ])) 
		{
			foreach ($data[ 2 ] as $entry) {
				if (is_array($entry->functions))
					$issue = implode("\n", $entry->functions);
				else
					$issue = $entry->functions;
				
				$status = 'bad_functions';
				
				parent::handleLog('file_integrity_php_bad_functions', $entry->file, false, $issue, $status);
			}
		}
	}
}