<?php
/**
 * $Id: jd_action.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined ('_JEXEC') or die('Restricted Access');

/**
 * Class that makes actions on log records. Fixes, deletes, ignores, quarantines, etc. 
 * !!! You can add any items for ignoring|deleting|fixing - logIds, filenames, etc.. The class
 * deletes|ignores files only if the items are numeric, and they are treated as log ids
 * 
 * Uses Singletone
 * 
 * @author nurlan
 *
 */
class JD_Action extends JObject
{
	var $_ignores = null;

	var $_deleteLogs = null;
	
	var $_fixes = null;
	
	var $_accepts = null;
	
	var $_exceptions = null;
	
	var $_quarantines = null;
	
	/**
	 * entodo: Implement blocking files in the next version
	 * @var unknown_type
	 */
	var $_blocks = null;
	
	/**
	 * The component configuration
	 * @var JParameter
	 */
	var $_componentConfig = null;
	
	/**
	 * @static
	 * @param unknown_type $type
	 * @return JD_Action instance :0
	 */
	function & getInstance($type = null) {
		static $instances = array();
		
		if (empty($type))
			return $instances;
		
		$type = JFolder::makeSafe($type);
		
		if (empty($instances[$type])) {
			JD_Action::_loadActions();
			$className = 'JD_'.$type.'_Action';
			
			if (class_exists($className))
				$instances[$type] = new $className;
			else {
				JError::raiseWarning(0, JText::_('Cannot instanciate action class').': '.'jd_'.$type.'_action');
				return null;
			}
		}
		return $instances[$type];
	}
	
	/**
	 * Load the actions
	 * @static
	 * @return unknown_type
	 */
	function _loadActions() {
		$dir = JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_jdefender'.DS.'lib'.DS.'actions';
		foreach (glob($dir.DS.'jd_*_action.php') as $file)
			require_once $file;
	}
	
	function __construct() {
		parent::__construct();
		
		$this->_ignores 	= array();
		$this->_accepts 	= array();
		$this->_deleteLogs 	= array();
		$this->_fixes 		= array();
		$this->_exceptions 	= array();
		$this->_blocks		= array();
		$this->_quarantines = array();
		
		jimport ('joomla.filesystem.file');
		jimport ('joomla.filesystem.folder');
		
		jimport ('joomla.application.component.model');
	}
	
	/**
	 * Add item to ignore
	 * @param $item
	 * @return unknown_type
	 */
	function addIgnore($item) {
		settype($item, 'array');
		$this->_ignores = array_merge($this->_ignores, $item);
	}
	
	/**
	 * Add item to delete
	 * @param unknown_type $item
	 * @return unknown_type
	 */
	function addToDelete($item) {
		settype($item, 'array');
		$this->_deleteLogs = array_merge($this->_deleteLogs, $item);
	}
	
	/**
	 * Add log record to accept the changes
	 * @param $item
	 * @return unknown_type
	 */
	function addAccept($item) {
		settype($item, 'array');
		$this->_accepts = array_merge($this->_accepts, $item);
	}
	
	/**
	 * Add item to fix
	 * @param $item
	 * @return unknown_type
	 */
	function addFix($item) {
		settype($item, 'array');
		$this->_fixes = array_merge($this->_fixes, $item);
	}
	
	/**
	 * Add item to quarantine
	 * @param unknown_type $item
	 * @return unknown_type
	 */
	function addQuarantine($item) {
		settype($item, 'array');
		$this->_quarantines = array_merge($this->_quarantines, $item);
	}
	
	/**
	 * Add item to quarantine
	 * @param unknown_type $item
	 * @return unknown_type
	 */
	function addException($item) {
		settype($item, 'array');
		$this->_exceptions = array_merge($this->_exceptions, $item);
	}
	
	/**
	 * Add item to block
	 * @param $item
	 * @return unknown_type
	 */
	function addBlock($item) {
		settype($item, 'array');
		$this->_blocks = array_merge($this->_blocks, $item);
	}
	
	/**
	 * Perform the previously added actions
	 * @return multitype:
	 */
	function performActions() {
		$result = array();
		
		if (count($this->_ignores)) {
			$result['ignore'] = $this->ignore();
		}
		if (count($this->_accepts)) {
			$result['accept'] = $this->accept();
		}
		if (count($this->_deleteLogs)) {
			$result['delete'] = $this->delete();
		}
		if (count($this->_fixes)) {
			$result['fix'] = $this->fix();
		}
		if (count($this->_blocks)) {
			$result['block'] = $this->block();
		}
		if (count($this->_quarantines)) {
			$result['block'] = $this->quarantine();
		}
		if (count($this->_exceptions)) {
			$result['exception'] = $this->makeException();
		}
		
		return $result;
	}
	
	/**
	 * Ignores the files|log recodrs|whatever by simple deleting the log entries
	 * Note that processed items are removed from buffer
	 * * @return boolean result
	 */
	function ignore() {
		$db = & JFactory::getDBO();
		
		if (!empty($this->_ignores) && count($this->_ignores)) {
			$ids = array();
			
			foreach ($this->_ignores as $k => $item) {
				if (is_numeric($item)) {
					$ids [] = $item;
					// Unset the item
					unset($this->_ignores[ $k ]);
				}
			}
			
			$logModel = & JModel::getInstance('Log', 'JDefenderModel');
			$this->_ignores = array();
			
			return $logModel->delete($ids);
		}
		
		$this->_ignores = array();
		
		return false;
	}
	
	/**
	 * @abstract
	 * @return unknown_type
	 */
	function delete() {
		$this->setError( JText::_('Action is not supported'));
		return false;
	}
	
	/**
	 * @abstract
	 * @return unknown_type
	 */
	function fix() {
		$this->setError( JText::_('Action is not supported'));
		return false;
	}
	
	/**
	 * @abstract
	 * @return unknown_type
	 */
	function makeException() {
		$this->setError( JText::_('Action is not supported'));
		return false;
	}
	
	/**
	 * @abstract
	 * @return unknown_type
	 */
	function accept() {
		$this->setError( JText::_('Action is not supported'));
		return false;
	}
	
	/**
	 * @abstract
	 * @return unknown_type
	 */
	function quarantine() {
		$this->setError( JText::_('Action is not supported'));
		return false;
	}
	
	/**
	 * @abstract
	 * @return unknown_type
	 */
	
	function block() {
		$this->setError( JText::_('Action is not supported'));
		return false;
	}
	
	/**
	 * Set the log status, and update the log ctime
	 * @param $ids Array of log ids.
	 * @param $status the status to set
	 * @return boolean|int False on error
	 * @final
	 */
	function setLogStatus($ids, $status) {
		if (empty($ids))
			return false;
		
		$db = & JFactory::getDBO();
		settype($ids, 'array');
		JArrayHelper::toInteger($ids);
		
		$q = 'UPDATE #__jdefender_log SET ctime = NOW(), status = '.$db->Quote($status).' WHERE id IN ('.implode(',', $ids).')';
		$db->setQuery( $q );
		if (!$db->query()) {
			$this->setError(JText::_('Cannot set log status'));
			return false;
		}
		
		$res = $db->getAffectedRows();
		
		if (!$res)
			$res = true;
		
		return $res;
	}
	
	/**
	 * Removes the files from filesystem table
	 * @param $filenames array containing filenames|urls to delete.
	 * @param $useFilter boolean Use the default component filter for files and dirs
	 * @return int|boolean number of deleted records|true if none deleted
	 */
	function removeFromFilesystemTable($filenames = array(), $useFilter = false) {
		$filesystemModel = & JModel::getInstance('Filesystem', 'JDefenderModel');
		
		return $filesystemModel->deleteFiles($filenames, $useFilter);
	}
	
	/**
	 * entodo: complete, test
	 * @param $files array Files that are to be refreshed
	 * @param $useFilter boolean
	 * @return unknown_type
	 */
	function refreshFilesystemTable($files = array(), $useFilter = true) {
		if (empty($files))
			return false;
		
		$filesystemModel = & JModel::getInstance('Filesystem', 'JDefenderModel');
		
		return $filesystemModel->rehashFiles($files, $useFilter);
	}
	
	function & getConfig() {
		if (empty($this->_componentConfig)) {
			$confModel 	= & JModel::getInstance('Configuration', 'JDefenderModel');
			$this->_componentConfig	= $confModel->getData(false);
		}
		return $this->_componentConfig;
	}
	
	function _getFilesAndIds($array) {
		$files 	= array();
		$ids 	= array();
			
		foreach ($array as $item) {
			if (is_numeric($item))
				$ids [] = $item;
			else
				$files [] = $item;
		}
		
		if (count($ids)) {
			$db = & JFactory::getDBO();
			$q 	= 'SELECT id, url FROM #__jdefender_log WHERE id IN ('.implode(', ', $ids).')';
			
			$db->setQuery( $q );
			$items = $db->loadObjectList();
			
			foreach ($items as $file) {
				if (empty($file->url) || JString::substr($file->url, 0, 7) == 'http://')
					continue;
				
				$files ['id:'.$file->id] = $file->url;
			}
		}
		
		$files = array_unique($files);
		
		return array($files, $ids);
	}
}
