<?php
/**
 * $Id: jd_php_injection_log_reader.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined ('_JEXEC') or die('Restricted Access');

require_once JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_jdefender'.DS.'lib'.DS.'log_readers'.DS.'jd_abstract_injection_log_reader.php';

/**
 * PHP injection log reader 
 * @author nurlan
 *
 */
class JD_Php_Injection_Log_Reader extends JD_Abstract_Injection_Log_Reader
{
	function __construct() {
		parent::__construct();
	}
}