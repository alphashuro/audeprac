<?php
/**
 * $Id: jd_alert.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined ('_JEXEC') or die('Access Restricted');

class JD_Alert extends JObject
{
	var $_params;
	
	/**
	 * @abstract Override this method to provide pre-configured instance
	 * @param string $type
	 */
	function & getInstance($type = null) 
	{
		static $instances = array();
		
		jimport ('joomla.filesystem.file');
		$type = JFile::makeSafe($type);
		
		if (empty($instances[ $type ])) 
		{
			if (empty($type))
			{
				$instances[$type] = new JD_Alert();
			}
			else 
			{
				$filename = JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_jdefender'.DS.'lib'.DS.'alerts'.DS.$type.DS.'jd_'.$type.'_alert.php';
				
				if (is_file($filename))
					require_once $filename;
				
				$className = 'jd_'.$type.'_alert';
				$instance = null;
				if (method_exists($className, 'getInstance'))
					$instance = call_user_func_array(array($className, 'getInstance'), array());
					 
				$instances[ $type ] = $instance;
			}
		}
		
		return $instances[ $type ];
	}
	
	function getTypes() {
		$types = array();
		$files = glob(JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_jdefender'.DS.'lib'.DS.'alerts'.DS.'*');
		
		for ($i = 0, $count = count($files); $i < $count; $i++ ) {
			if (is_dir($files[ $i ])) {
				$types[] = basename($files[ $i ]);
			}
		}
		
		return $types;
	}
	
	/**
	 * Abstract method for sending alerts
	 * @abstract
	 * @param string $message
	 */
	function send($message) 
	{
		$types 	= $this->getTypes();
		$params = & $this->_getParams();
		
		$result = true;
		
		foreach ($types as $type) {
			if ($params->get('alerts_'.$type.'_enabled')) {
				$transport = & JD_Alert::getInstance($type);
				if (empty($transport)) {
					$this->setError(JText::_('Cannot find transport').': '.$type);
					$result = false;
				}
				
				if ( ! $transport->send($message)) {
					$this->setError($transport->getError());
					$result = false;
				}
			}
		}
		
		return $result;
	}
	
	/**
	 * Gets current alerter configuration
	 * @access protected
	 */
	function & _getParams() {
		if (empty($this->_params))
		{
			$model = & JModel::getInstance('Configuration', 'JDefenderModel');
			$this->_params = new JParameter($model->getIni());
		}
		
		return $this->_params;
	}
}