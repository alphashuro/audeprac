<?php
/**
 * $Id: jd_logger.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined ('_JEXEC') or die('Restricted Access');

/**
 * Utility class that provides logging facilities
 * @author nurlan
 *
 */
class JD_Logger extends JObject
{
	var $_context;
	
	var $_loggingEnabled;
	
	function __construct($context) 
	{
		parent::__construct();
		
		jimport ('joomla.error.log');
		
		if (JString::substr($context, -4) != '.php')
			$context .= '.php';
		else
			$context = 'mighty_defender_log.php';

		$this->_context 		= $context;
		$this->_loggingEnabled 	= defined(JDEBUG) && JDEBUG;
	}
	
	/**
	 * @return JD_Log the logger instance
	 * @param unknown_type $context
	 */
	function & getInstance($context = null)
	{
		static $instances = array();
		
		if (empty($context))
			$context = 'mighty_defender_log';
		
		if (empty($instances[ $context ]))
		{
			$instances[ $context ] = new JD_Logger($context);
		}
		
		return $instances[ $context ];
	}
	
	/**
	 * @static
	 */
	function log($status, $comment) 
	{
		$log = & JLog::getInstance($this->_context);
		
		$entry = array(
			'status' 	=> $status,
			'comment' 	=> $comment
		);
		
		return $log->addEntry($entry);
	}
	
	
	function isLoggingEnabled()
	{
		return $this->_loggingEnabled;
	}
	
	function setLoggingEnabled($value)
	{
		return $this->_loggingEnabled = !!$value;
	}
}