<?php
/**
 * $Id: jd_first_scan_handler.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined ('_JEXEC') or die('Restriced Access');

/**
 * This class inserts 
 * @author Nurlan
 *
 */
class jd_first_scan_handler extends JD_Handler
{
	function __construct() {
		parent::__construct();
	}
	
	/**
	 * Handle the first filesystem scan. note that $data variable holds just the filenames
	 * @return unknown_type
	 */
	function handleResults(&$results) {
		$this->_handleFilesystemIntegrity($results['file_integrity'][1]);
		
		// Remove the entries.
		$results['file_integrity'][1] = array();
	}
	
	function _handleFilesystemIntegrity(&$data) {
		$filesystemModel = & JModel::getInstance('Filesystem', 'JDefenderModel');
		return $filesystemModel->addFiles($data);
	}
}