<?php
/**
 * $Id: jd_file_integrity_php_jexec_action.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined ('_JEXEC') or die('Restricted Access');

require_once JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_jdefender'.DS.'lib'.DS.'actions'.DS.'jd_file_integrity_php_bad_functions_action.php';

class JD_File_Integrity_Php_Jexec_Action extends JD_File_Integrity_Php_Bad_Functions_Action
{
	var $fixString = '<?php defined("_JEXEC") or die("Restricted Access"); /* Mighty Defender FIX */ ?>'; 
	
	function __construct() {
		parent::__construct();
	}
	
	/**
	 * Fix the missing _JEXEC issue
	 * Note that processed items are removed from buffer
	 * (non-PHPdoc)
	 * @see components/com_jdefender/lib/actions/JD_Action#fix()
	 */
	function fix() {
		if (empty($this->_fixes))
			return 0;
		
		list($files, $ids) = $this->_getFilesAndIds($this->_fixes);
		
		
		$count = 0;
		
		$fixedFiles = array();
		$fixedIds 	= array();
		
		// now, fix the files.
		$error = false;
		foreach ($files as $k => $file) {
			if (JFile::exists($file)) 
			{
				$dir = dirname($file);
				
				$f = new JD_File($file);
				$contents = $f->read();
				
				if (!$f->write($this->fixString.$contents)) 
				{
					$error = true;
					$this->setError(JText::_('Cannot write to file').': '.$f->getError());
					continue;
				}
				
				$fixedFiles [] = $file;
				if (strpos($k, ':')) {
					$id = explode(':', $k);
					$id = $id [ 1 ];
					
					$fixedIds [] = $id;
				}
				$count ++;
			}
		}
		
		parent::setLogStatus($fixedIds, 'fixed');
		parent::refreshFilesystemTable($fixedFiles);
		// Empty array
		$this->_fixes = array();
		
		return $error ? false : $count;
	}
	
	// entodo: !!!COMPLETE
	/**
	 * Accepts the file, and adds an exception for it.
	 */
	function accept() 
	{
		JError::raiseError(500, 'Not implemented');
		$ids = $this->_accepts;
		JArrayHelper::toInteger($ids);
		$ids = array_unique($ids);
		
		parent::setLogStatus($ids, 'accepted');
		
		$this->_accepts = array();
		
		return false;
	}
	
	function block($block = true) {
		return parent::block($block);
	}
}