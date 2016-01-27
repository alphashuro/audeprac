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
defined ('_JEXEC') or die();

jimport ('joomla.application.component.model');

class JDefenderModelScan extends JModel
{
	var $_options_data = null;
	var $_filesystem_data = null;
	
	function __construct() {
		parent::__construct();
		
		require_once JPATH_COMPONENT_ADMINISTRATOR.DS.'lib'.DS.'scanners'.DS.'filters'.DS.'jd_filesystem_filter.php';
  		require_once JPATH_COMPONENT_ADMINISTRATOR.DS.'lib'.DS.'scanners'.DS.'jd_scanner.php';
		require_once JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'vars.php';
	}
	
	function getLastScanDate() {
		
		return JD_Vars_Helper::getVar('last_scan_date', 'jdefender');
	}
	
	/**
	 * 
	 * @param $path
	 * @param $doLog
	 */
	function getScanData($path = '', $doLog = false) {
		$configModel = & JModel::getInstance('Configuration', 'JDefenderModel');
		
		$params = new JParameter($configModel->getIni());
		
		JD_Scanner::loadScanner();
		
  		$fsScanner = false;
  		if ( !$this->getState('filesystem.scanned') )
  		{
	  		$fsScanner = & JD_Filesystem_Scanner::getInstance();
	  		$fsScanner->loadValidator();
	  		
	  		// Set the "first scan" flag.
	  		if ($this->_isFirstScan()) {
		  		foreach ($fsScanner->listeners as $k => $v) {
		  			if (method_exists($v, 'setFirstScan')) {
		  				$fsScanner->listeners[ $k ]->setFirstScan(true);
		  			}
		  		}
	  		}
	  		
	  		
	  		if ($doLog)
	  			JD_Vars_Helper::setVar('status', 'jdefender_scan', JText::_('Scanning filesystem'));
	  		
	  		// Run scanners
	  		if (empty($this->_filesystem_data))
	  			$this->_filesystem_data = $fsScanner->scan($path);
  		}
  		
  		
  		$optScanner = false;
  		if ( !$this->getState('options.scanned') )
  		{
	  		$optScanner = & JD_Options_Scanner::getInstance();
	  		$optScanner->loadValidator();
	  		
	  		
	  		if ($doLog)
	  			JD_Vars_Helper::setVar('status', 'jdefender_scan', JText::_('Scanning system settings'));
	  			
	  		// scan
	  		if (empty($this->_options_data))
	  		{
	  			$this->_options_data = $optScanner->scan();
	  			$this->setState('options.scanned', true);
	  		}
  		}
  		
  		// If filesystem scan ended
  		if (!empty($this->_filesystem_data['EOF']))
  		{	
  			$this->setState('filesystem.scanned', true);
  			unset($this->_filesystem_data['EOF']);
  		}
  		
  		$result = array(
  			'filesystem' => & $this->_filesystem_data,
  			'options' => & $this->_options_data
  		);

  		return $result;
	}
	
	function _isFirstScan() {
		$lastScanDate = $this->getLastScanDate();
		return empty($lastScanDate) || $lastScanDate == $this->_db->getNullDate();
	}
}