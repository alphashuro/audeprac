<?php
/**
 * $Id: include.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined('_JEXEC') or die('Restricted Access');


$baseDir 	= JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_jdefender'.DS.'lib';
$helperPath = JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_jdefender'.DS.'helpers';

jimport ('joomla.filesystem.file');
jimport ('joomla.filesystem.folder');

require_once JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_jdefender'.DS.'helpers'.DS.'configuration.php';

// utilities
require_once $baseDir.DS.'utils'.DS.'jd_debug.php';
require_once $baseDir.DS.'utils'.DS.'jd_file.php';
require_once $baseDir.DS.'utils'.DS.'jd_ftp.php';
require_once $baseDir.DS.'utils'.DS.'jd_logger.php';
require_once $baseDir.DS.'utils'.DS.'jd_error.php';

// scanners
require_once $baseDir.DS.'scanners'.DS.'jd_scanner.php';
require_once $baseDir.DS.'scanners'.DS.'filters'.DS.'jd_filesystem_filter.php';

require_once $baseDir.DS.'validators'.DS.'jd_validator.php';
require_once $baseDir.DS.'handlers'.DS.'jd_handler.php';
require_once $baseDir.DS.'html'.DS.'toolbar.php';
require_once $baseDir.DS.'log_readers'.DS.'jd_log_reader.php';
// actions
require_once $baseDir.DS.'actions'.DS.'jd_action.php';
require_once $baseDir.DS.'actions'.DS.'jd_action_manager.php';

require_once $baseDir.DS.'rules'.DS.'jd_abstract_rule.php';
require_once $baseDir.DS.'rules'.DS.'jd_rule_manager.php';


// Helpers
require_once $helperPath.DS.'block.php';
require_once $helperPath.DS.'configuration.php';
require_once $helperPath.DS.'vars.php';
require_once $helperPath.DS.'spam.php';
require_once $helperPath.DS.'log.php';
require_once $helperPath.DS.'admin_menu.php';
require_once $helperPath.DS.'scan.php';