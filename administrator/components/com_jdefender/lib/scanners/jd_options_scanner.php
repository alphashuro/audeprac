<?php
/**
 * $Id: jd_options_scanner.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined('_JEXEC') or die('Restricted access');


class JD_Options_Scanner extends JD_Scanner
{	
	var $_new_version;
	
	/**
	 * @override
	 * @return JD_Options_Scanner
	 */
	function & getInstance()
	{
		static $instance = 0;
		
		if (!$instance) {
			$instance = new JD_Options_Scanner();
		}

		return $instance;
	}
	
	function __construct() {
		parent::__construct();
		$this->_new_version = null;
	}
	
	function scan() {
		$result = array();
		
		$result['ftp'] 				= $this->_isFtpEnabled();
		$result['admins'] 			= $this->_isAdminUsernameChanged();
		$result['joomlaVersion']	= $this->_getJoomlaVersions();
		
		return $result;
	}
	
	/**
	 * Loads validators
	 * @param $name validator name
	 * @return string classname of loaded validator
	 */
	function loadValidator($name = '', $type = '') {
		return;
	}
	
	function _isFtpEnabled() {
		$config = new JConfig();
		return !!$config->ftp_enable;
	}
	
	function _isAdminUsernameChanged() {
		$uri = & JFactory::getURI();
		
		$db = & JFactory::getDBO();
		
		
		$host = $uri->getHost();
		
		$shortenedHost = $host;
		if (strpos($host, 'www.') === 0)
			$shortenedHost = substr($host, 4);
			
		$pos = strpos($shortenedHost, '.');
		if ($pos !== false)
			$shortenedHost = substr($shortenedHost, 0, $pos);
			
		$easyUsernames = array(
			'"admin"', '"root"', '"administrator"', '"administrat0r"','"test"', '"tester"', '"user"', $db->Quote($host), $db->Quote($shortenedHost)
		);
		
		$q = 'SELECT '.
				'id, username '.
			'FROM #__users '.
				'WHERE '.
			'gid BETWEEN 23 AND 25 AND username IN ('.implode(',', $easyUsernames).') ';
		
		$db->setQuery( $q );
			
		$admins = $db->loadObjectList();

		if (!$admins || !count($admins)) {
			return true;
		}
		
		return $admins;
	}
	
	function _getJoomlaVersions() {
		$version = array( '', '' );
		
 		//get current Joomla version
        $curVersion = new JVersion();
		$version[0] = $curVersion->getShortVersion();
			
		$version[0] = explode('.',$version[0]);
		if ($version[0][2] <= '9'){
			$version[0][2] = '0'.$version[0][2];
		}
		$version[0] = $version[0][0].'.'.$version[0][1].'.'.$version[0][2];
		
		$version[1] = $this->getLatestJoomlaVersion();
		
		// return false
		return $version;
	}
	
	function getLatestJoomlaVersion() {
		if (!$this->_new_version) {
			$downloadsPage = @file_get_contents('http://www.joomla.org/download.html');
			if ($downloadsPage) {
				preg_match('/\b1\.5\.\d+\b/', $downloadsPage, $results);
			
				$version = null;
				if (count($results))
					$version = $results[ 0 ];
					
				foreach ($results as $v) {
					list($maj, $mid, $min) 			= explode('.', $v);
					list($maxMaj, $maxMid, $maxMin) = explode('.', $version);
					
					if ($min > $maxMin)
						$version = $v;
				}
				
				$this->_new_version = $version;
			}
		}
		
		return $this->_new_version;
	}
}