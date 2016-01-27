<?php
/**
 * $Id: view.html.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined("_JEXEC") or die("Restricted Access");

jimport('joomla.application.component.view');

class JDefenderViewScan extends JView
{
	function display($tpl = null)
	{
		global $mainframe, $option;

		$db 		= & JFactory::getDBO();
		$document 	= & JFactory::getDocument();
		
		$fsModel 	= & JModel::getInstance('Filesystem', 'JDefenderModel');
		$scanModel 	= & JModel::getInstance('Scan', 'JDefenderModel');
		
		$document->addStyleSheet(JURI::root().'administrator/components/com_jdefender/css/toolbar.css');
		
		$nullDate 	= $db->getNullDate();
		$lastScanDate = $scanModel->getLastScanDate();
		
		$needScan = false;
		if (!$lastScanDate || $nullDate == $lastScanDate) {
			$lastScanDate = '<span style="color:red">'.JText::_('Never').'</span>';
			$needScan = true;
		}
		
		$totalFiles = $fsModel->getScanDataTotal();
		
		$safeMode = ini_get('safe_mode');

		
		if ($safeMode) {
			JError::raiseNotice(120, JText::_('PHP safe mode is enabled on your server. This version of Mighty Defender does not support system scan with PHP safe mode on'));
		}
		
		$this->assignRef('needScan', 		$needScan);
		$this->assignRef('lastScanDate', 	$lastScanDate);
		$this->assignRef('totalFiles',		$totalFiles);
		$this->assignRef('safeMode',		$safeMode);
		
		$this->assign('scanLink', JRoute::_('index.php?option=com_jdefender&controller=scan&task=scan'));
		
		JToolBarHelper::title(JText::_('Scanner'), 'scan');
		JD_Admin_Menu_Helper::decorate();
		
		parent::display($tpl);
	}
}
