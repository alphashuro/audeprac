<?php
/**
 * $Id: jd_filesystem_filter.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined ('_JEXEC') or die('Restricted Access');

// Windows compatibility
if(!function_exists('fnmatch')) {
    function fnmatch($pattern, $string) {
        return preg_match("#^".strtr(preg_quote($pattern, '#'), array('\*' => '.*', '\?' => '.'))."$#i", $string);
    }
}

/**
 * Filter for {@link JD_Filesystem_Scanner}
 * @author Nurlan
 *
 */
class JD_Filesystem_Filter extends JObject
{
	var $excludedDirs;
	var $excludedFiles;
	var $permittedFilePatterns;
	var $excludedDirPatterns;
	
	function __construct($filePatterns = array(), $excludedDirs = array(), $excludedFiles = array(), $excludedDirPatterns = array()) {
		parent::__construct();
		
		$this->excludedDirs  			= array();
		$this->excludedFiles 			= array();
		$this->permittedFilePatterns 	= array();
		$this->excludedDirPatterns		= array();
		
		$this->addFilePattern($filePatterns);
		$this->addExcludeDirs($excludedDirs);
		$this->addExcludeFiles($excludedFiles);
		$this->addExcludedDirPatterns($excludedDirPatterns);
	}
	
	function isDirOK( $folder ) {
		if (@$this->excludedDirs[ $folder ])
			return false;
		
		foreach ($this->excludedDirs as $dir => $flag) {
			if (!$flag)
				continue;
			
			if ($this->_inDir($folder, $dir)) {
				return false;
			}
		}
		
//		// check for excluded directory patterns
//		$basename = basename($folder);
//		foreach ($this->excludedDirPatterns as $pattern) {
//			if (fnmatch($pattern, $basename)) {
//				return false;
//			}
//		}
		
		if (preg_match('/'.$this->getExcludedDirPregexp().'/', $folder))
			return false;
		
		return true;
	}
	
	function isFileOK($file, $checkPatterns = false) {
		$fOK = true;
		
		$file = JFile::stripExt($file).'.'.strtolower(JFile::getExt($file));
		if ($checkPatterns) {
			$fOK = false; 
			foreach ($this->permittedFilePatterns as $p) {
				if (fnmatch($p, $file)) {
					$fOK = true;
					break;
				}
			}
		}
		
		
		
		if ($fOK) {
			foreach ($this->excludedDirs as $dir => $flag) {
				if (!$flag)
					continue;
				if ($this->_inDir($file, $dir)) {
					$fOK = false;
					break;
				}
			}
		}
		
		if ($fOK) {
			// if the file was excluded
			if (isset($this->excludedFiles[ $file ]) && $this->excludedFiles[ $file ])
				$fOK = false;
		}
		
		return $fOK;
	}
	
	function addExcludedDirPatterns($patterns) {
		settype($patterns, 'array');
		$this->excludedDirPatterns = array_merge($this->excludedDirPatterns, $patterns);
		$this->_excludedDirRegExp = null;
	}
	
	function removeExcludedDirPatterns($patterns) {
		settype($patterns, 'array');
		$this->excludedDirPatterns = array_diff($this->excludedDirPatterns, $patterns);
		$this->_excludedDirRegExp = null;
	}
	
	/**
	 * Regular expression
	 * @var string
	 */
	var $_excludedDirRegExp = null;
	
	function getExcludedDirPregexp() {
		if (!is_array($this->excludedDirPatterns) || !count($this->excludedDirPatterns))
			return false;
			
		if (empty($this->_excludedDirRegExp))
		{	
			$searchReplace = array(
				'\\*' => '.*',
				'\?' => '.'
			);
			
			$patterns = $this->excludedDirPatterns;
			
			foreach ($patterns as $k => $v)
				$patterns[ $k ] = preg_quote($v);
				
			$patterns = str_replace(array_keys($searchReplace), $searchReplace, $patterns);
			
			$this->_excludedDirRegExp = implode('|', $patterns);
		}
		return $this->_excludedDirRegExp;
	}

	function addFilePattern($patterns) {
		settype($patterns, 'array');
		
		$this->permittedFilePatterns = array_merge($this->permittedFilePatterns, $patterns); 
	}
	
	function removeFilePattern($patterns) {
		settype($patterns, 'array');
		
		$this->permittedFilePatterns = array_diff($this->permittedFilePatterns, $patterns);
	}
	
	function addExcludeDirs($dirs) {
		settype($dirs, 'array');
		
		$dirs = array_map(array('JPath', 'clean'), $dirs);
		
		foreach ($dirs as $dir) {
			$dir = rtrim($dir, DS);		
			$this->excludedDirs[ $dir ] = true;
		}
	}
	
	function removeExcludeDirs($dirs) {
		settype($dirs, 'array');
		
		$dirs = array_map(array('JPath', 'clean'), $dirs);
		
		foreach ($dirs as $dir) {
			$dir = rtrim($dir, DS);
			$this->excludedDirs[ $dir ] = false;
		}
		
		return $res;
	}
	
	function addExcludeFiles($files) {
		settype($files, 'array');
		
		$files = array_map(array('JPath', 'clean'), $files);
		
		foreach ($files as $file)
			$this->excludedFiles[ $file ] = true;
	}
	
	function removeExcludeFiles($files) {
		settype($files, 'array');
		
		$files = array_map(array('JPath', 'clean'), $files);
		
		foreach ($files as $f)
			$this->excludedFiles[ $f ] = false;
	}
	
	function _inDir($file, $dir) {
		return strpos($file, $dir) === 0;
	}
}