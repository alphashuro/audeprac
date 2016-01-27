<?php
/**
 * $Id: configuration.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined ('_JEXEC') or die ('Restricted Access');

class JDefenderConfigurationHelper extends JObject
{
	var $validatorParams = null;
	var $validatorGroups = null;
	
	var $alertsParams	= null;
	
	var $_mainParams 	= null;
	
	var $_ini = null;
	
	function __construct() {
		parent::__construct();
	}
	/**
	 * @return JDefenderConfigurationHelper  
	 */
	function & getInstance() {
		static $instance = 0;
		if (!$instance) {
			$instance = new JDefenderConfigurationHelper();
		}
		
		return $instance;
	}

	/**
	 * Fetches component parameters
	 * @return JParameter 
	 */
	function & getMainParams() {
		if (empty($this->_mainParams)) {
			$model = & JModel::getInstance('Configuration', 'JDefenderModel');
			$this->_mainParams = & $model->getData();
		}
		return $this->_mainParams;
	}
	
	/**
	 * Fetches validator parameters
	 * @param $withXML boolean Get the XML file too
	 * @return array of JParameter 
	 */
	function & getValidatorParams($withXML = false) {
		if (empty($this->validatorParams)) {
			$this->validatorParams = array();
			
			$validatorsPath		= JPATH_COMPONENT_ADMINISTRATOR.DS.'lib'.DS.'validators';
			$validatorGroups 	= & $this->getValidatorGroups();
			
			foreach (array_keys($validatorGroups) as $group) {
				$path = $validatorsPath.DS.$group.DS.$group.'.xml';
				if (JFile::exists($path)) {
					if ($withXML)
						$this->validatorParams[ $group ] = new JParameter($this->_getIni(), $path);
					else
						$this->validatorParams[ $group ] = new JParameter($this->_getIni());
				}
			}
		}
		
		return $this->validatorParams;
	}
	
	/**
	 * Fetches alerts parameters
	 * @return JParameter 
	 */
	function & getAlertsParams() {
		if (empty($this->alertsParams)) {
			$xml = JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_jdefender'.DS.'lib'.DS.'alerts'.DS.'alerts.xml';
			$this->alertsParams = new JParameter($this->_getIni(), $xml);
		}
		
		return $this->alertsParams;
	}
	
	function & getValidatorGroups() {
		if (empty($this->validatorGroups)) {
			$this->validatorGroups = array();
			
			$basePath 	= JPATH_COMPONENT_ADMINISTRATOR.DS.'lib'.DS.'validators';
			$oldDir 	= getcwd();
			
			chdir($basePath);
			$validatorGroups = glob('*', GLOB_ONLYDIR);
			chdir($oldDir);
			
			foreach ($validatorGroups as $g) {
				$validators = array_map(array(&$this, '_cleanValidatorName'), glob($basePath.DS.$g.DS.'*.php'));
				$this->validatorGroups[ $g ] = $validators;
			}
		}
		return $this->validatorGroups;
	}
	
	function & _getIni() {
		if (empty($this->_ini)) {
			$model = & JModel::getInstance('Configuration', 'JDefenderModel');
			$this->_ini = $model->getIni();
		}
		
		return $this->_ini;
	}
	
	function _cleanValidatorName($name) {
		return preg_replace(array('/^jd_/', '/_validator.*/'), '', basename($name));
	}
	
}