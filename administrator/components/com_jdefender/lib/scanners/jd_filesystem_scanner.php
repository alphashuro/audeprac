<?php
/**
 * $Id: jd_filesystem_scanner.php 7770 2012-01-26 12:39:15Z kostya $
 * $LastChangedDate: 2012-01-26 18:39:15 +0600 (Thu, 26 Jan 2012) $
 * $LastChangedBy: kostya $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined ('_JEXEC') or die;

class JD_Filesystem_Scanner extends JD_Scanner
{
	/**
	 * 
	 * @var JD_Filesystem_Filter
	 */
	var $filter;
	var $_isReadFiles;
	var $_options;
	
	var $_filesScanned;
	var $_foldersScanned;
	
	/**
	 * If no filter is given, read params from the global settings
	 * @param $filter
	 * @return unknown_type
	 */
	function __construct($filter = false) {
		parent::__construct('filesystem');
		
		jimport ('joomla.filesystem.file');
		jimport ('joomla.filesystem.folder');
		
		if (!$filter) {
			$fileExts 				= explode("\n", trim($this->_jd_options->get('scan_file_patterns', '*'), "\n "));
			$excludedDirs 			= explode("\n", trim($this->_jd_options->get('scan_excluded_directories', JPATH_ROOT.DS.'cache'."\n".JPATH_ROOT.DS.'tmp'), "\n "));
			$excludedDirPatterns	= explode("\n", trim($this->_jd_options->get('scan_excluded_directory_patterns', '.svn'."\n".'.CVS'), "\n "));
			
			$fileExts 				= str_replace(array("\r", "\n"), '', $fileExts);
			$excludedDirs 			= str_replace(array("\r", "\n"), '', $excludedDirs);
			$excludedDirPatterns 	= str_replace(array("\r", "\n"), '', $excludedDirPatterns);
			
			for ($i = 0, $count = count($excludedDirs); $i < $count; $i++) {
				$excludedDirs[ $i ] = JPath::clean(JPATH_ROOT.DS.$excludedDirs[ $i ]);
			}
		
			$filter = new JD_Filesystem_Filter($fileExts, $excludedDirs, array(), $excludedDirPatterns);
		}
		
		$this->filter = $filter;
			
		$this->_isReadFiles = null;
		$this->_filesScanned = 0;
		$this->_foldersScanned = 0;
	}
	
	/**
	 * Fetches the filesystem scanner with a given filter.
	 * @param $filter string Filename filter, ex '*.php', creates scanner with default filter if no filter passed 
	 * @return JD_Filesystem_Scanner
	 */
	function & getInstance($filter = null)
	{
		static $instances = array();
		
		$sign = md5(serialize($filter));
		
		if (empty($instances[ $sign ]))
			$instances[ $sign ] = new JD_Filesystem_Scanner($filter);

		return $instances[ $sign ];
	}
	
	/**
	 * Scans the filesystem
	 * @param $baseDir the base directory
	 * (non-PHPdoc)
	 * @see administrator/components/com_jdefender/lib/scanners/JD_Scanner#scan()
	 */
	function scan($baseDir = JPATH_ROOT, $useFile = true) 
	{
		$session = & JFactory::getSession();
		$filelist 	= $useFile ? $session->get('fileList', false, 'jdefender') : false;
		$filepos 	= (int)$session->get('fileListPosition', 0, 'jdefender');
		$isEof		= false;
		
		$files	= false;
		$dirs 	= false;
		if ($filelist && is_file($filelist)) 
		{
			$files = array();
			$dirs = array();
			
			$h = @fopen($filelist, 'r');
			
			if ($h) {
				if ($filepos)
					fseek($h, $filepos);
				
				for ($i = 0, $c = 50 + mt_rand(0, 15); $i < $c && !feof($h); $i++) 
				{
					$line = @fgets($h);
					
					if ($line) {
						$line = trim($line);
						
						if (is_file($line))
							$files [] = $line;
						elseif (is_dir($line))
							$dirs [] = $line;
					}
				}
			}
			
			$pos 	= @ftell($h);
			$isEof 	= feof( $h );
			$session->set('fileListPosition', $pos, 'jdefender');
			
			
			@fclose($h);
		}
		 
//		var_dump($filelist, $pos, $isEof);die;
		
		// Restore variables from old session.
		$this->_filesScanned 	= JD_Vars_Helper::getVar('files', 'jdefender_scan');
		$this->_foldersScanned 	= JD_Vars_Helper::getVar('dirs', 'jdefender_scan');
		
		
		$this->scanFilesAndDirs($baseDir, $files, $dirs);
		
		$return = array();
		$result = $this->trigger('onGetData');
		for ($i = 0, $c = count($result); $i < $c; $i++)
		{
			// index by validator name
			$return[ $result[ $i ][ 0 ] ] = & $result[ $i ]; 
		}
		
		if ($isEof) {
			$return['EOF'] = true;
		}
		
		return $return;
	}
	
	function isReadFiles() {
		if (is_null($this->_isReadFiles)) 
		{	
			$res = $this->trigger('onGetOptions');
			
			$this->_isReadFiles = false;
			
			foreach ($res as $option) {
				if (isset($option['readFile']) && $option['readFile']) {
					$this->_isReadFiles = true;
					break; 
				}
			}
		}
		return $this->_isReadFiles;
	}
	
	function scanFilesAndDirs($baseDir, $theFiles = false, $theDirs = false)
	{
		$session = & JFactory::getSession();
		$doLog = $session->get('doLog', false, 'jdefender');
		
		$baseDir 	= JPath::clean($baseDir);
		// Remove the trailing slash
		if ( in_array(substr($baseDir, -1), array('/', '\\')) )
			$baseDir = substr($baseDir, 0, -1);
		
		$files 	= array();
		$dirs 	= array();
		
		if ($theFiles !== false)
			$files = $theFiles;
		else 
			$files = JFolder::files($baseDir, '.', true, true, array_keys($this->filter->excludedDirs));
		
		if ($theDirs !== false)
			$dirs = $theDirs;
		else
			$dirs = $this->_listFolders($baseDir, $this->filter->getExcludedDirPregexp(), true, true, array_keys($this->filter->excludedDirs));
		
		foreach ($files as $file) {
			$contents = null;
			
			if (!$this->filter->isFileOK($file, true))
				continue;
			
			if ($this->isReadFiles()) {
				$f = new JD_File($file);
				$contents = $f->read($file);
				
				if (false === $contents)
					$contents = null;
			}
				
			$this->trigger('onFile', array($file, &$contents));
			
			$this->_filesScanned++;
		}
		
		if ( $doLog )
			JD_Vars_Helper::setVar('files', 'jdefender_scan', $this->_filesScanned);
		
		foreach ($dirs as $dir) {
			if (!$this->filter->isDirOK($dir))
				continue;
				
			$this->trigger('onDir', array(&$dir));
			$this->_foldersScanned++;
		}
		
		
		if ( $doLog )
			JD_Vars_Helper::setVar('dirs', 'jdefender_scan', $this->_foldersScanned);
	}
	
	/**
	 * Silent version of JFolder::files() 
	 * @param $path
	 * @param $filter
	 * @param $recurse
	 * @param $fullpath
	 * @param $exclude
	 */
	function _listFolders($path, $filter = '.', $recurse = false, $fullpath = false, $exclude = array('.svn', 'CVS'))
	{
		// Initialize variables
		$arr = array();

		// Check to make sure the path valid and clean
		$path = JPath::clean($path);

		// Is the path a folder?
		if (!is_dir($path)) {
			$this->setError(JText::_('Path is not a folder'), 'Path: ' . $path);
			return false;
		}

		// read the source directory
		$handle = opendir($path);
		while (($file = readdir($handle)) !== false)
		{
			if (($file != '.') && ($file != '..') && (!in_array($file, $exclude))) {
				$dir = $path . DS . $file;
				$isDir = is_dir($dir);
				if ($isDir) {
					// Removes filtered directories
					if (!preg_match("/$filter/", $file)) {
						if ($fullpath) {
							$arr[] = $dir;
						} else {
							$arr[] = $file;
						}
						
						if ($recurse) {
							if (is_integer($recurse)) {
								$arr2 = $this->_listFolders($dir, $filter, $recurse - 1, $fullpath, $exclude);
							} else {
								$arr2 = $this->_listFolders($dir, $filter, $recurse, $fullpath, $exclude);
							}
							
							$arr = array_merge($arr, $arr2);
						}
					}
					
				}
			}
		}
		closedir($handle);

		asort($arr);
		return $arr;
	}
	/**
	 * Loads validator(s)
	 * (non-PHPdoc)
	 * @see administrator/components/com_jdefender/lib/scanners/JD_Scanner#loadValidator($name)
	 * @override
	 */
	function loadValidator($names = false) {
		if (!$names) {
			$names = glob(JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_jdefender'.DS.'lib'.DS.'validators'.DS.'filesystem'.DS.'*.php');
			foreach ($names as $k => $v) 
				$names[ $k ] = basename($v);
			
			$names = array_map(array(&$this, '_getValidatorName'), $names);
		}
		
		settype($names, 'array');
		
		parent::loadValidator($names, 'filesystem');
	}
	
	function _getValidatorName($filename) {
		return JFile::makeSafe(
			preg_replace(
				array('/^jd_/', '/_validator(?:.+)/'), 
				array('', ''), 
				$filename
			)
		);
	}
}