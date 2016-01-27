<?php
/**
 * $Id: filesystem.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined ('_JEXEC') or die('Restricted access');

class TableFilesystem extends JTable
{
	var $id;
	var $filename;
	var $fullpath;
	var $permission;
	var $ctime;
	var $mtime;
	var $size;
	/**
	 * Values: 'file' | 'dir'
	 * @var string
	 */
	var $type;
	var $gid;
	var $uid;
	var $hash_md;
	var $scandate;
	
	var $contents;
	
 	function __construct(&$db) {
 		if (empty($db))
 			$db = & JFactory::getDBO();
 		
		parent::__construct('#__jdefender_filesystem', 'id', $db);
	}
	
	function store() {
		if (empty($this->date)) {
			$now = & JFactory::getDate();
			$this->scandate = $now->toMySQL();
		}
		
		
		return parent::store();
	}
	
	/**
	 * Load filesystem record by filename
	 * @param $filename
	 */
	function loadByFilename($filename) 
	{
		if (empty($filename))
			return false;
		
		$db = & JFactory::getDBO();
		$db->setQuery( 'SELECT * FROM #__jdefender_filesystem WHERE fullpath = '.$db->Quote($filename).' LIMIT 1' );
		
		$data = $db->loadObject();
		
		if (empty($data))
			return false;
			
		return $this->bind($data);
	}
	
	/**
	 * Load properties from a given file properties
	 * 
	 * @param $file string The filename to scan
	 * @param $contents boolean Load the contents
	 * @param $loadId boolean Load id from database
	 * @return boolean result
	 */
	function loadFromFile($file, $contents = false, $loadId = false) {
		if (!JFile::exists($file) && !JFolder::exists($file.DS))
			return false;
			
		$info 				= @stat($file);
		
		$this->scandate		= $this->_db->getNullDate();
		$this->filename 	= basename($file);
		$this->fullpath 	= $file;
		$this->permission 	= fileperms($file) & 0777;
		$this->size			= filesize($file);
		
		$ctime = & JFactory::getDate($info['ctime']);
		$mtime = & JFactory::getDate($info['mtime']);
		
		$this->ctime		= $ctime->toMySQL();
		$this->mtime		= $mtime->toMySQL();
		
		$this->uid			= $info['uid'];
		$this->gid			= $info['gid'];
		
		$this->type 		= '';
		if (is_file($file)) {
			$this->type 	= 'file';
			$this->hash_md 	= md5_file($file);
			
			if ($contents) {
				$f = new JD_File($file);	
				$this->contents = $f->read();
			}
		}
		elseif (is_dir($file))
			$this->type = 'dir';
			
		
		if ($loadId) {
			$this->_db->setQuery('SELECT id FROM #__jdefender_filesystem WHERE fullpath = '.$this->fullpath.' LIMIT 1');
			$this->id = $this->_db->loadResult();
		}
		
		return true;
	}
	
	/**
	 * Compares given file info with the current one
	 * @param unknown_type $info
	 * @param unknown_type $vars
	 * @return Ambigous <string, stdClass>
	 */
	function compare($info, $vars = false) {
		if (!$vars)
			$vars = $this->_getVars();
		
		$mismatch = new stdClass;
		
		if (is_object($info))
			$info = get_object_vars($info);
		
		$found = false;
		foreach ($vars as $v) {
			if ($this->$v != @$info[ $v ]) {
				$mismatch->$v = array($this->$v, @$info[ $v ]);
				$found = true; 
			}
		}
		
		return $found ? $mismatch : false;
	}
	
	function _getVars() {
		static $vars = 0;
		if (!$vars)
			$vars = array('id', 'filename', 'fullpath', 'permission', 'ctime', 'mtime', 'size', 'type', 'gid', 'uid', 'hash_md', 'date');
			
		return $vars; 
	}
}