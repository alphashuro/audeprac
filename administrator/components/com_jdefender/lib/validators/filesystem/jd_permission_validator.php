<?php
/**
 * $Id: jd_permission_validator.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined ('_JEXEC') or die('Restricted access');

class JD_Permission_Validator extends JD_Validator 
{
	var $files;
	var $directories;
	
	var $dirPermission;
	var $filePermission;
	
	function __construct() {
		parent::__construct('permission', 'filesystem');
		
		$this->files 		= array();
		$this->directories 	= array();
		
		$this->dirPermission 	= intval($this->_params->get('permission_max_dir_permission', '755'), 8);
		$this->filePermission 	= intval($this->_params->get('permission_max_file_permission', '644'), 8);
	}
	
	function onFile( &$file ) {
		$perm = @fileperms( $file ) & 0777;
			
		if (!$this->fileOK($perm)) {
			$r = new stdClass;
			$r->file = $file;
			$r->permission = $perm;
			
			$this->files [] = $r;
			return false;
		}
		return true;
	}
	
	function onDir( &$dir ) {
		$perm = @fileperms( $dir ) & 0777;
		
		if (!$this->dirOK($perm)) {
			$r = new stdClass;
			$r->dir = $dir;
			$r->permission = $perm;
			
			$this->directories [] = $r;
			return false;
		}
		
		return true;
	}
	
	function onGetData() {
		return array($this->_name, $this->directories, $this->files);
	}
	
	
	function dirOK($perm) {
		$owner = ((7 << 6) & $perm) 	<= ((7 << 6) & $this->dirPermission);
		$group = ((7 << 3) & $perm) 	<= ((7 << 3) & $this->dirPermission);
		$other = (7 & $perm) 			<= (7 & $this->dirPermission);
		
		return $owner && $group && $other;
	}
	
	function fileOK($perm) {
		$owner = ((7 << 6) & $perm) 	<= ((7 << 6) & $this->filePermission);
		$group = ((7 << 3) & $perm) 	<= ((7 << 3) & $this->filePermission);
		$other = (7 & $perm) 			<= (7 & $this->filePermission);
		
		return $owner && $group && $other;
	}
}