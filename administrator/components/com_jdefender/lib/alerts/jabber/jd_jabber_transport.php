<?php
/**
 * $Id: jd_jabber_transport.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined('_JEXEC') or die('Restricted Access');

// This class handles all events fired by the Jabber client class; you
// can optionally use individual functions instead of a class, but this
// method is a bit cleaner.
class JD_Jabber_Transport extends JObject 
{
	var $username;
	var $password;
	var $server;
	
	var $jabber;
	
	var $MAX_RUN_TIME 		= 5;
	var $CALLBACK_FREQUENCY = 0.5;
	
	/**
	 * The message queue
	 * @var array
	 */
	var $messageQueue;
	
	function __construct($username, $password, $server) {
		parent::__construct();
		
		$this->username = $username;
		$this->password = $password;
		$this->server 	= $server;
		
		$this->messageQueue = array();
		
		// include the Jabber class
		require_once JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_jdefender'.DS.'lib'.DS.'alerts'.DS.'jabber'.DS.'class_Jabber.php';
		
		// create an instance of the Jabber class
		$this->jabber = new Jabber($display_debug_info = false);
		
		$this->_initialize();
	}
	
	
	function _initialize() {
		// set handlers for the events we wish to be notified about
		$this->jabber->set_handler("authenticated",	$this, "handleAuthenticated");
		$this->jabber->set_handler("authfailure",	$this, "handleAuthFailure");
		$this->jabber->set_handler("heartbeat",		$this, "handleHeartbeat");
		$this->jabber->set_handler("error",			$this, "handleError");
		$this->jabber->set_handler("connected",		$this, "handleConnected");
	}
	
	function addMessage($username, $messageText) {
		$this->messageQueue [] = array(
			'user' 		=> $username,
			'message' 	=> $messageText
		);
	}
	
	/**
	 * Connect to jabber server
	 */
	function connect() {
		if (!$this->jabber->connect($this->server)) {
			$this->setError(JText::_('Cannot connect to jabber server').': '.$this->server);
			return false;
		}
		
		$this->jabber->get_roster();
		
		return true;
	}
	
	/**
	 * Disconnect from jabber server
	 */
	function disconnect() {
		$this->jabber->disconnect();
	}
	
	function execute() {
		$this->jabber->execute($this->CALLBACK_FREQUENCY, $this->MAX_RUN_TIME);
	}
	
	function handleHeartbeat() {
		// Send the message queue
		
		if (!count($this->messageQueue) || empty($this->messageQueue)) {
			$this->jabber->terminated = true;
			return;
		}
		
		$i 		= 0;
		$count 	= 3;
		
		foreach ($this->messageQueue as $k => $message) { 
			$this->jabber->message($message['user'], $type = "normal", $id = NULL, $message['message']);
			
			unset($this->messageQueue[ $k ]);
			
			if ($i >= $count)
				break;
				
			$i++;
		}
	}
	
	
	// called when a connection to the Jabber server is established
	function handleConnected() {
		$this->jabber->login($this->username, $this->password);
	}
	
	// called after a login to indicate the the login was successful
	function handleAuthenticated() {
		// browser for transport gateways
		$this->jabber->browse();
		
		// set this user's presence
		$this->jabber->set_presence("","Online");
	}
	
	// Called after a login to indicate that the login was NOT successful
	function handleAuthFailure($code, $error) {
		$this->jabber->terminated = true;
		$this->setError(JText::_('Authentication failed'));
	}
	
	// called when an error is received from the Jabber server
	function handleError($code, $error, $xmlns) {
		if (is_array($error))
			$error = JArrayHelper::toString($error);
		
		$this->setError(JText::_('Jabber Transport error').': '.JText::_('Code').': '.$code.', '.JText::_('Error').': '.$error);
	}
}
