<?php
/**
 * $Id: jd_ftp.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined ('_JEXEC') or die("Restricted Access");

/**
 * FTP Fetcher ;)
 * @author nurlan
 *
 */
class JD_Ftp extends JObject
{
	/**
	 * @return JFTP handle
	 */
	function & getFtpHandle()
	{
		static $ftp = null;
		
		if (is_null($ftp))
		{
			// Initialize variables
			jimport('joomla.client.helper');
			$FTPOptions = JClientHelper::getCredentials('ftp');
	
			if ($FTPOptions['enabled'] == 1) {
				// Connect the FTP client
				jimport('joomla.client.ftp');
				$ftp = & JFTP::getInstance($FTPOptions['host'], $FTPOptions['port'], null, $FTPOptions['user'], $FTPOptions['pass']);
			}
			else {
				$ftp = false;
			}
		}

		return $ftp;
	}
	
	function translatePath($file)
	{
		if (empty($file))
			return null;
		
		jimport('joomla.client.ftp');
		// Initialize variables
		jimport('joomla.client.helper');
		$FTPOptions = JClientHelper::getCredentials('ftp');
		
		if (JD_Ftp::getFtpHandle())
		{
			// Translate path for the FTP account and use FTP write buffer to file
			return JPath::clean(str_replace(JPATH_ROOT, $FTPOptions['root'], $file), '/');
		}

		return $file;
	}
	
//	/**
//	 * Chmod 
//	 * @param string $path Untranslated path
//	 * @param mixed $mode File mode
//	 */
//	function chmod($path, $mode) {
//		$path = JD_Ftp::translatePath($path);
//		// If no filename is given, we assume the current directory is the target
//		if ($path == '') {
//			$path = '.';
//		}
//
//		// Convert the mode to a string
//		if (is_int($mode)) {
//			$mode = decoct($mode);
//		}
//		
//		$ftp = & JD_Ftp::getFtpHandle();
//
//		if (!$ftp)
//		{
//			return false;
//		}
//		
//		// If native FTP support is enabled lets use it...
//		if (FTP_NATIVE) {
//			if (@ftp_site($ftp->_conn, 'CHMOD '.$mode.' '.$path) === false) {
//				if($ftp->_OS != 'WIN') {
//					JError::raiseWarning('35', 'JFTP::chmod: Bad response' );
//				}
//				return false;
//			}
//			return true;
//		}
//
//		// Send change mode command and verify success [must convert mode from octal]
//		if (!$ftp->_putCmd('CHMOD '.$mode.' '.$path, array(200, 250))) 
//		{
//			if (!$ftp->_putCmd('SITE CHMOD '.$mode.' '.$path, array(200, 250))) 
//			{
//				if ($ftp->_OS != 'WIN') {
//					JError::raiseWarning('35', 'JFTP::chmod: Bad response', 'Server response: '.$ftp->_response.' [Expected: 200 or 250] Path sent: '.$path.' Mode sent: '.$mode);
//				}
//				return false;
//			}
//			return false;
//		}
//		return true;
//	}
}