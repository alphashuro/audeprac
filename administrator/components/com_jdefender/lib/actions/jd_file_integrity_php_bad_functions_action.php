<?php
/**
 * $Id: jd_file_integrity_php_bad_functions_action.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined ('_JEXEC') or die();

require_once JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_jdefender'.DS.'lib'.DS.'actions'.DS.'jd_abstractfileaction.php';


class JD_File_Integrity_Php_Bad_Functions_Action extends JD_AbstractFileAction
{
	function __construct() {
		parent::__construct();
	}
	
	
	
	/**
	 * Blocks/unblocks file execution
	 * @param boolean $block Block / Unblock
	 */
	function block($block = true) {
		require_once JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_jdefender'.DS.'helpers'.DS.'block.php';
		
		list($files, $ids) = $this->_getFilesAndIds($this->_blocks);
		
		$fixedFiles = array();
		$fixedIds = array();
		
		foreach ($files as $key => $file) {
			if ( ! is_file($file) || JFile::getExt($file) != 'php')
				continue;
			
			$id = false;
			if (strpos($key, ':') !== false) {
				$tmp = explode(':', $key);
				$id = (int)$tmp[ 1 ];
			}
			
			if ($block)
				$result = JD_Block_Helper::blockPHP($file);
			else
				$result = JD_Block_Helper::unblockPHP($file);
			
			if ($result !== true) {
				$this->setError($result);
				continue;
			}
						
			$fixedFiles [] = $file;
			if ($id)
				$fixedIds [] = $id;
		}
		
		
		$cFiles = 0; $cIds = 0;
		
		if (count($fixedFiles))
			$cFiles = parent::refreshFilesystemTable($fixedFiles);
		
		if (count($fixedIds))
			$cIds = parent::setLogStatus($fixedIds, $block ? 'blocked' : 'unblocked');
			
		
		$this->_blocks = array();
		
		return max($cFiles, $cIds);
	}
}