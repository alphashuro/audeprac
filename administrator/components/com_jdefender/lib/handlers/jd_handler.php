<?php
/**
 * $Id: jd_handler.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined ('_JEXEC') or die('Restricted access');

class JD_Handler extends JObject
{
	var $_params;
	
	var $_logData;
	
	var $_maxLogBufferSize = 50;
	
	function __construct() {
		parent::__construct();
		
		$model = & JModel::getInstance('Configuration', 'JDefenderModel');
		
		$this->_params 	= new JParameter($model->getIni());
		
		require_once JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_jdefender'.DS.'lib'.DS.'utils'.DS.'jd_extension_resolver.php';
	}
	
	/**
	 * Returns new instance of JD_Handler.
	 * @param $type string
	 * @static
	 * @return JD_Handler
	 */
	function getInstance($type, $family) {
		JD_Handler::loadHandlers($family);
		
		$className = 'JD_'.ucfirst($type).'_Handler';
		
		if (class_exists($className)) {
			return new $className;
		}
		return null;
	}
	
	/**
	 * Load the handlers :)
	 * @static
	 * @return void
	 */
	function loadHandlers($type) 
	{
		static $loaded = array();
		
		if (!empty($loaded[$type]))
			return;
		
		$type = JFolder::makeSafe($type);
		$basePath = JPATH_COMPONENT_ADMINISTRATOR.DS.'lib'.DS.'handlers'.DS.$type;

		$files = JFolder::files($basePath, 'jd_.+handler\.php', false, false);
		
		foreach($files as $file) {
			$path = $basePath.DS.$file;
			if (JFile::exists($path))
				require_once $path;
		}
		
		$loaded[$type] = true;
	}
	
	/**
	 * Abstract function that every handler implement
	 * @abstract
	 * @return unknown_type
	 */
	function handleResults(&$result) {
		JError::raiseError(404, 'JD_Handler::handleResults not implemented');
		return false;
	}
	
	/**
	 * Logs the JDefender log message, and preforms automatic fixes
	 * @param $logType
	 * @param $url
	 * @param $data
	 * @param $status
	 * @return unknown_type
	 */
	function handleLog($logType, $url, $data, $issue = '', $status = '')
	{		
		$log = new stdClass;
		
		$log->type 		= $logType;
		$log->url 		= $url;
		$log->post 		= serialize($data);
		$log->status 	= $status;
		$log->issue		= $issue;
		
		$actionManager = & JD_Action_Manager::getInstance();
		
		$log = $actionManager->addActionFromLog($log);
		
		if (strpos($log->url, 'http://') === false && file_exists($log->url)) {
			$ext = JD_Extension_Resolver::resolveExtension($log->url);
			
			if ($ext)
				$log->extension = $ext->type.'::'.$ext->group.'::'.$ext->extension;
		}
		
		$this->_logData[] = & $log;
		
		if (count($this->_logData) >= $this->_maxLogBufferSize) {
			// Flush the log buffer
			if (!$this->flushLogs()) {
				JError::raiseError(1234, JText::_('Error writing log').' - '.$this->getError());
				return false;
			}
			return true;
		}
	}
	
	function _getLogger() {
		$session = & JFactory::getSession();
		$doLog = $session->get('doLog', false, 'jdefender');

		$log = false;
		
		if ( $doLog ) {
			jimport ('joomla.error.log');
			$log = & JLog::getInstance('defender_scan.php');
		}
		
		return $log;
	}
	
	/**
	 * Flushes the log buffer.
	 * @return boolean
	 */
	function flushLogs() {
		if (!count($this->_logData))
			return true;
		
		$session = & JFactory::getSession();
		$doLog 	= $session->get('doLog', false, 'jdefender');
		
		$db 	= & JFactory::getDBO();
		$keys 	= array('id', 'ip', 'ctime', 'type', 'user_id', 'url', 'post', 'cook', 'referer', 'status', 'issue', 'extension');
		
		$q = 'INSERT INTO #__jdefender_log '.
		  '(`id`, `ip`, `ctime`, `type`, `user_id`, `url`, `post`, `cook`, `referer`, `status`, `issue`, `extension`) ';
		
		
		$count 	= array();
		$values = array();
		foreach ($this->_logData as $entry) 
		{
			if ($doLog)
			{
				if (empty($count[$entry->type]))
					$count[$entry->type] = 0;
				$count[$entry->type]++;
			}

			
			$row = array();
			foreach ($keys as $key) {
				if (empty($entry->$key)) {
					if ($key == 'ctime')
						$row [] = 'NOW()';
					else
						$row [] = '""';
				}
				else
					$row [] = $db->Quote($entry->$key);
			}
			
			$values [] = '( '. implode(', ', $row).' )';
		}
		
		$q = $q . ' VALUES '.implode(', ', $values);
		
		$db->setQuery( $q );
		if (!$db->query()) {
			$this->setError(JText::_('Cannot write log'));
			return false;
		}
		
		
		if ($doLog && count($count))
		{
			foreach ($count as $type => $c) {
				$new = (int)JD_Vars_Helper::getVar($type, 'jdefender_scan', 0) + $c;
				JD_Vars_Helper::setVar($type, 'jdefender_scan', $new);
			}
		}
		
		// Empty the buffer
		$this->_logData = null;
		
		return true;
	}
}