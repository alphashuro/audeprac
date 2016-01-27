<?php
/**
 * $Id: options_toolbar_helper.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined ('_JEXEC') or die('Restricted Access');


class ME_Defender_Options_Toolbar_Helper extends JObject
{
	var $_toolbar;
	
	function __construct() {
		parent::__construct();
	}
	
	/**
	 * @returns ME_Defender_Options_Toolbar_Helper
	 */
	function getInstance() {
		static $instance = 0;
		if (!$instance) {
			$instance = new ME_Defender_Options_Toolbar_Helper();
		}
		
		return $instance;
	}
	
	/**
	 * Add the needed buttons according to the given log type
	 * @param string $type The log type
	 * @return void
	 */
	function makeButtons($type, $extension = false, $status = null, $url = null) {
		$toolbar = & $this->getToolbar();
		
		
		$toolbar->ignoreFile('Clean Log');
		
		switch (strtolower($type)) {
			case 'file_integrity_new':
				$toolbar->viewFile('View File', $url);
				$toolbar->acceptFile('Accept');
				
				if ($status != 'blocked' || is_null($status))
					$toolbar->block('Block Execution', true);
				if ($status == 'blocked' || is_null($status))
					$toolbar->block('Unblock Execution', false);
					
				$toolbar->deleteFile('Delete Files');
				break;
				
			case 'file_integrity_changed':
				$toolbar->viewFile('View File', $url);
				$toolbar->acceptFile('Accept');
				$toolbar->fixFile('Restore');
				
				if ($status != 'blocked' || is_null($status))
					$toolbar->block('Block Execution', true);
				if ($status == 'blocked' || is_null($status))
					$toolbar->block('Unblock Execution', false);
					
				$toolbar->deleteFile('Delete Files');
				break;
			
			case 'file_integrity_php_bad_functions':
				$toolbar->viewFile('View File', $url);
				// $toolbar->acceptFile('Accept');
				$toolbar->deleteFile('Delete Files');
				
				if ($status != 'blocked' || is_null($status))
					$toolbar->block('Block Execution', true);
				if ($status == 'blocked' || is_null($status))
					$toolbar->block('Unblock Execution', false);
									
//				if ($extension)
//					$toolbar->reportDeveloper('Inform Developer', $extension);
				break;
			
			case 'file_integrity_php_jexec':
				$toolbar->viewFile('View File', $url);
				
				if ($status != 'fixed')
					$toolbar->fixFile('Fix');
				
				if ($status != 'blocked' || is_null($status))
					$toolbar->block('Block Execution', true);
				if ($status == 'blocked' || is_null($status))
					$toolbar->block('Unblock Execution', false);
				
				if ($extension)
					$toolbar->reportDeveloper('Inform Developer', $extension);
//				$toolbar->deleteFile('Delete Files');
				break;
				
			case 'folder_safety_index':
				// $toolbar->acceptFile('Accept');
				if ($status != 'fixed')
					$toolbar->fixFile('Fix');
					
				$model = & JModel::getInstance('Log', 'JDefenderModel');
				$count = $model->getNotCount('folder_safety_index', 'fixed');
				
				if ($count)
					$toolbar->fixFolderSafetyAllFile('Fix All');
				
				// $toolbar->deleteFile('Delete File');
				break;
			
			case 'permission':
				$toolbar->viewFile('View File', $url);
				$toolbar->acceptFile('Accept');
				if ($status != 'fixed')
					$toolbar->fixFile('Fix');
				$toolbar->deleteFile('Delete Files');
				break;
				
			case 'missing_file':
				$toolbar->acceptFile('Accept Changes');
				break;
				
//			case 'blocked_users_ips':
//				$toolbar->acceptFile('Accept Changes');
//				break;
				
			case 'flood':
				if ($status != 'blocked')
					$toolbar->blockIp('Block IP', true);
				if ($status == 'blocked' || $status == null)
					$toolbar->blockIp('Unblock IP', false);
				
				$toolbar->purgeBlockedIps('Clean Blocked IP Logs');
				break;
			
			case 'php_injection':
			case 'sql_injection':
				if ($status != 'added_to_exceptions')
					$toolbar->addException('Add exception');
				if ($status != 'blocked')
					$toolbar->blockIp('Block IP', true);
				if ($status == 'blocked' || $status == null)
					$toolbar->blockIp('Unblock IP', false);
				
				$toolbar->purgeBlockedIps('Clean Blocked IP Logs');
				break;
			
			case 'options':
				break;
		}
		
		if (JRequest::getInt('refresh'))
			$toolbar->closeRefresh('Close');
		else
			$toolbar->close('Close');
	}
	
	/**
	 * Get the ME_Defender Toolbar :))
	 * @return ME_Defender_Toolbar
	 */
	function & getToolbar() {
		if (empty($this->_toolbar)) {
			require_once JPATH_COMPONENT.DS.'lib'.DS.'html'.DS.'toolbar.php';
			$this->_toolbar = new ME_Defender_Toolbar('toolbar');

			$this->_toolbar->inIFrame = (JRequest::getCmd('tmpl') == 'component');
			
			$document = & JFactory::getDocument();
			$document->addStyleSheet(JURI::base().'components/com_jdefender/css/toolbar.css');
			$document->addScript(JURI::base().'components/com_jdefender/js/toolbar.js');
		}
		return $this->_toolbar;
	}
}