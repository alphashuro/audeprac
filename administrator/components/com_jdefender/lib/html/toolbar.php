<?php
/**
 * $Id: toolbar.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined ('_JEXEC') or die("Not you day");

jimport ('joomla.html.toolbar');

/**
 * The toolbar class :)
 * @author nurlan
 *
 */
class ME_Defender_Toolbar extends JToolBar
{
	/**
	 * 
	 * @var boolean
	 */
	var $inIFrame;
	
	function __construct($name) {
		parent::__construct($name);
		
		$inIFrame = true;
	}
	
	function acceptFile($title)
	{
		$onclick = $this->_getSubmitOnClick('accept');
			
    	$html = '<a href="javascript:void(0);" onclick="'.$onclick.'" class="toolbar">'
			.'<span class="icon-32-apply" type="Standard"></span>'
			.JText::_($title)
			.'</a>';
  
		parent::appendButton( 'Custom', $html, 'accept' );
	}
	
	function fixFile($title) {
		$onclick = $this->_getSubmitOnClick('fix');
		
		$class = $title == 'Restore' ? "icon-32-restore" : "icon-32-fix"; 
		
		$html = '<a href="javascript:void(0);" onclick="'.$onclick.';" class="toolbar">'
			.'<span class="'.$class.'" type="Standard"></span>'
			.JText::_($title)
			.'</a>';
  
		parent::appendButton( 'Custom', $html, 'fix' );
	}
	
	function deleteFile($title) {
		$onclick = $this->_getSubmitOnClick('delete', false, true);
		
		$html = '<a href="javascript:void(0);" onclick="'.$onclick.'" class="toolbar">'
			.'<span class="icon-32-delete" type="Standard"></span>'
			.JText::_($title)
			.'</a>';
  
		parent::appendButton( 'Custom', $html, 'delete' );
	}
	
	function blockIp($title, $block = false) 
	{	
		if ($block) {
			$class = 'icon-32-lock';
			$task = 'lockIP';
		}
		else {
			$class = 'icon-32-unlock';
			$task = 'unlockIP';
		}
		
		$onclick = $this->_getSubmitOnClick($task);
		
		$html = '<a href="javascript:void(0);" onclick="'.$onclick.';" class="toolbar">'
			.'<span class="'.$class.'" type="Standard"></span>'
			.JText::_($title)
			.'</a>';
  
		parent::appendButton( 'Custom', $html, 'delete' );
	}
	
	function block($title, $block) 
	{	
		if ($block) {
			$class = 'icon-32-lock';
			$task = 'blockFile';
		}
		else {
			$class = 'icon-32-unlock';
			$task = 'unblockFile';
		}
		
		$onclick = $this->_getSubmitOnClick($task);
		
		$html = '<a href="javascript:void(0);" onclick="'.$onclick.';" class="toolbar">'
			.'<span class="'.$class.'" type="Standard"></span>'
			.JText::_($title)
			.'</a>';
  
		parent::appendButton( 'Custom', $html, 'delete' );
	}
	
	function addException($title) {
		$onclick = $this->_getSubmitOnClick('addException');
		
		$html = '<a href="javascript:void(0);" onclick="'.$onclick.'" class="toolbar">'
			.'<span class="icon-32-new" type="Standard"></span>'
			.JText::_($title)
			.'</a>';
  
		parent::appendButton( 'Custom', $html, 'ignore' );
	}
	
	function ignoreFile($title) {
		$onclick = $this->_getSubmitOnClick('ignore');
		
		$html = '<a href="javascript:void(0);" onclick="'.$onclick.'" class="toolbar">'
			.'<span class="icon-32-ignore" type="Standard"></span>'
			.JText::_($title)
			.'</a>';
  
		parent::appendButton( 'Custom', $html, 'ignore' );
	}
	
	function quarantineFile($title) {
		$onclick = $this->_getSubmitOnClick('quarantine');
		
		$html = '<a href="javascript:void(0);" onclick="'.$onclick.'" class="toolbar">'
			.'<span class="icon-32-quarantine" type="Standard"></span>'
			.JText::_($title)
			.'</a>';
  
		parent::appendButton( 'Custom', $html, 'quarantine' );
	}
	
	function reportDeveloper($title, $extension) {
		$onclick = $this->_getSubmitOnClick('reportDeveloper', $extension, false);
		
		$html = '<a href="javascript:void(0);" onclick="'.$onclick.'" class="toolbar">'
			.'<span class="icon-32-send" type="Standard"></span>'
			.JText::_($title)
			.'</a>';
  
		parent::appendButton( 'Custom', $html, 'quarantine' );
	}
	
	function closeRefresh($title) {
		$onclick = $this->_getSubmitOnClick('close', 'refresh');
		
		$html = '<a href="javascript:void(0);" onclick="'.$onclick.'" class="toolbar">'
			.'<span class="icon-32-cancel" type="Standard"></span>'
			.JText::_($title)
			.'</a>';
  
		parent::appendButton( 'Custom', $html, 'close' );
	}
	
	function close($title) {
		$onclick = $this->_getSubmitOnClick('close');
		
		$html = '<a href="javascript:void(0);" onclick="'.$onclick.'" class="toolbar">'
			.'<span class="icon-32-cancel" type="Standard"></span>'
			.JText::_($title)
			.'</a>';
  
		parent::appendButton( 'Custom', $html, 'close' );
	}
	
	function purgeBlockedIps($title) {
		$onclick = $this->_getSubmitOnClick('purgeBlockedIps', false, false);
		
		$html = '<a href="javascript:void(0);" onclick="'.$onclick.'" class="toolbar">'
			.'<span class="icon-32-delete" type="Standard"></span>'
			.JText::_($title)
			.'</a>';
  
		parent::appendButton( 'Custom', $html, 'close' );
	}
	
	function fixFolderSafetyAllFile($title) {
		$onclick = $this->_getSubmitOnClick('fixFolderSafetyAll', false, false);
		
		$html = '<a href="javascript:void(0);" onclick="'.$onclick.'" class="toolbar">'
			.'<span class="icon-32-fix" type="Standard"></span>'
			.JText::_($title)
			.'</a>';
		
		parent::appendButton( 'Custom', $html, 'close' );
	}
	
	function viewFile($title, $file) {
		if (!$this->inIFrame)
			return;
			
		if (!is_file($file))
			return;
		
		$onclick = 'xajax_jdReadFile($(\'log_id\').value);';
		
		$html = '<a href="javascript:void(0);" onclick="'.$onclick.'" class="toolbar">'
			.'<span class="icon-32-fix" type="Standard"></span>'
			.JText::_($title)
			.'</a>';
		
		parent::appendButton( 'Custom', $html, 'close' );
	}
	
	function _getSubmitOnClick($task, $info = false, $alert = true) {
		$alertText = addslashes(JText::_('Please make a selection from the list'));
		
		if ($this->inIFrame) {
			$onclick = 'ME_Defender.Toolbar.submit(\''.$task.'\');';
			
			if ($task == 'close') 
			{
				if ($info == 'refresh')
					$onclick = 'ME_Defender.Toolbar.closeSqueezeNRedirect();';
				else
					$onclick = 'parent.SqueezeBox.close();';
			}
			
			if ($task == 'reportDeveloper') {
				$onclick = 'parent.document.forms[\'adminForm\'].filter_extension.value = \''.$info.'\'; parent.submitform(\'reportDeveloperLog\');';
			}
			
			if ($task == 'delete') 
			{
				$alertText = addslashes(JText::_('Deleting files can crash your web site. Are you sure?'));
				$onclick = 'if (confirm(\''.$alertText.'\')) {submitform(\''.$task.'Log\');}';
			}
		}
		else {
			if ($alert)
				$onclick = 'if(document.adminForm.boxchecked.value==0){alert(\''.$alertText.'\');} else {submitform(\''.$task.'Log\');}';
			else
				$onclick = 'submitform(\''.$task.'Log\');';
				
			
			if ($task == 'close')
				$onclick = 'submitform(\''.$task.'Log\');';
			
			if ($task == 'delete') 
			{
				$alertText = addslashes(JText::_('Deleting files can crash your web site. Are you sure?'));
				$onclick = 'if(document.adminForm.boxchecked.value==0){alert(\''.$alertText.'\');} else { if (confirm(\''.$alertText.'\')) {submitform(\''.$task.'Log\');}}';
			}
		}
		
		
		
		return $onclick;
	}
}
