<?php
/**
 * $Id: scan.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined ('_JEXEC') or die('Access Restricted');

/**
 * Scan process helper
 * @author nurlan
 *
 */
class JD_Scan_Helper extends JObject
{
	/**
	 * Set logging state
	 * @param boolean $enable
	 */
	function setLogging($enable = true) {
		$session = & JFactory::getSession();
		$session->set('doLog', $enable, 'jdefender');
	}
	
	function isLogging() {
		$session = & JFactory::getSession();
		return $session->get('doLog', false, 'jdefender');
	}
	
	/**
	 * Sets filelist
	 * @param mixed $filelist
	 */
	function setFilelist($filelist = false) {
		$session = & JFactory::getSession();
		
		if ($filelist) {
			$session->set('fileList', $filelist, 'jdefender');
  			$session->set('fileListPosition', 0, 'jdefender');
		}
		else {
			// Remove filelist
	  		$filelist = $session->get('fileList', false, 'jdefender');
			if ($filelist && is_file($filelist))
				@unlink($filelist);
			
			$session->clear('fileList', 		'jdefender');
  			$session->clear('fileListPosition', 'jdefender');
		}
	}
	
	function getFilelist() {
		$session = & JFactory::getSession();
		return $session->get('fileList', false, 'jdefender');
	}
	
	
	function setFilePosition($pos) {
		$session = & JFactory::getSession();
		return $session->set('fileListPosition', (int)$pos, 'jdefender');
	}
	
	function getFilePosition() {
		$session = & JFactory::getSession();
		return $session->get('fileListPosition', 0, 'jdefender');
	}
	
	/**
	 * Sets last scan date
	 * @param JDate $now
	 */
	function setLastScanDate($now = false) {
		if (empty($now) || ! @is_a($now, 'JDate'))
			$now = & JFactory::getDate();
		
  		JD_Vars_Helper::setVar('last_scan_date', 'jdefender', $now->toMySQL());
	}
		
	/**
	 * Cleanup session variables that are used in system scan. 
	 */
	function cleanUpState() {
		$session = & JFactory::getSession();
	  	
	  	JD_Vars_Helper::purgeVars('jdefender_scan', 0);
	  	
		// Reset flags
		$session->clear('doLog', 			'jdefender');
		// Unset counters
	  	$session->clear('filesystem.scanned', 	'jdefender');
	  	$session->clear('options.scanned', 		'jdefender');
	  	
	  	// Clean filelist data
		JD_Scan_Helper::setFilelist(false);
	}
}