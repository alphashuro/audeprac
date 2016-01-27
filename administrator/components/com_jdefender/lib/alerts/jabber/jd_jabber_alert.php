<?php
/**
 * $Id: jd_jabber_alert.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined ('_JEXEC') or die('Access Restricted');


class JD_Jabber_Alert extends JD_Alert
{
	var $_transport;
	var $_destination_usernames;
	/**
	 * @override
	 */
	function & getInstance()
	{
		static $instance = null;
		
		if (empty($instance))
		{
			$instance = new JD_Jabber_Alert();
		}
		
		return $instance;
	}
	
	/**
	 * Send messages
	 * @param mixed $messages Message or an array of messages to send 
	 */
	function send($messages) {
		settype($messages, 'array');
		
		$transport = & $this->_getTransport();
		$usernames = & $this->_getDestinationUsernames();
		
		foreach ($messages as $message) {
			$message = str_replace(array('<br>', '<br />'), "\n", $message);
			$message = strip_tags($message);
			
			foreach ($usernames as $user)
				$transport->addMessage($user, $message);
		}
		
		$transport->connect();
		$transport->execute();
		$transport->disconnect();
		
		return true;
	}
	
	/**
	 * Creates the jabber transport object
	 */
	function & _getTransport()
	{
		if (empty($this->_transport))
		{
			require_once JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_jdefender'.DS.'lib'.DS.'alerts'.DS.'jabber'.DS.'jd_jabber_transport.php';
			
			$params = & $this->_getParams();
			
			$username 	= $params->get('alerts_jabber_username');
			$password 	= $params->get('alerts_jabber_password');
			$server 	= $params->get('alerts_jabber_server');
			
			
			$this->_transport = new JD_Jabber_Transport($username, $password, $server);
		} 
		
		return $this->_transport;
	}
	
	/**
	 * Get the destination usernames
	 */
	function & _getDestinationUsernames() {
		if (empty($this->_destination_usernames)) {
			$params = & $this->_getParams();
			
			$usernames = trim($params->get('alerts_jabber_destination'), ", \r\n");
			$usernames = explode(',', $usernames);
			
			
			$validUsernames = array();
			
			foreach ($usernames as $k => $v) {
				$v = trim($v, ", \r\n");
				
				if ($this->_isValidUsername($v))
					$validUsernames [ ] = $v; 
			}
			
			$this->_destination_usernames = $validUsernames;
		}
		
		return $this->_destination_usernames;
	}
	
	function _isValidUsername($username) {
		// TODO: change. jabber id validation seems to be quite challenging ) 
		return ($username && strpos($username, '@') !== false && preg_match('/@.*\..*$/', $username));
	}
}