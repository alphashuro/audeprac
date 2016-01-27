<?php
/**
 * $Id: jd_extension_resolver.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined ('_JEXEC') or die('Access Restricted');

/**
 * Class that helps to determine extension by one of it's files
 * @author nurlan
 *
 */
class JD_Extension_Resolver extends JObject
{
	var $_keywords;
	
	function __construct() {
		parent::__construct();
		
		
		jimport ('joomla.filesystem.file');
	}
	
	/**
	 * Resolve extension by one of it's files
	 * @param $path
	 * @returns object 
	 * 
	 * $result->type = 'component';
	 * $result->isAdmin = $isAdmin;
	 * $result->group = null;
	 * $result->extension			
	 */
	function resolveExtension($path) {
		$originalPath = JPath::clean($path);
		
		$path = str_replace('\\', '/', $path);
		$path = str_replace(JPATH_ROOT, '', $path);
		$path = trim($path, '/ ');
		
		$isAdmin = strpos($path, 'administrator/') !== false;
		
		$parts = explode('/', $path);
		
		for ($i = 0, $c = count($parts); $i < $c; $i++) {
			switch ($parts[ $i ]) {
				case 'components':
					if (empty($parts[$i + 1]) || strpos($parts[$i + 1], 'com_') !== 0)
						break;
						
					$result = new stdClass;
					$result->type = 'component';
					$result->isAdmin = $isAdmin;
					$result->group = null;
					$result->extension = empty($parts[$i + 1]) ? '' : $parts[$i + 1];
					return $result;
					
				case 'plugins':
					if (empty($parts[$i + 1]) || empty($parts[$i + 2]))
						break;
					
					// check if we have plugin php or xml file ;) 
					$file = basename($parts[$i + 2]);
					$configFile = JPATH_ROOT.DS.'plugins'.DS.$parts[$i + 1].DS.JFile::stripExt($file).'.xml';
					$pluginFile = JPATH_ROOT.DS.'plugins'.DS.$parts[$i + 1].DS.JFile::stripExt($file).'.php';
					
					if (!is_file($configFile) || !is_file($pluginFile))
						break;
					
					$result = new stdClass;
					$result->type = 'plugin';
					$result->isAdmin = $isAdmin;
					$result->group = empty($parts[$i + 1]) ? '' : $parts[$i + 1];
					$result->extension = empty($parts[$i + 2]) ? '' : JFile::stripExt($parts[$i + 2]);
					return $result;
					
				case 'modules':
					if (empty($parts[$i + 1]))
						break;
						
					// check if we have module php or xml file ;) 
					$file = JFolder::makeSafe($parts[$i + 1]);
					$configFile = JPATH_ROOT.DS.'modules'.DS.$file.DS.JFile::stripExt($file).'.xml';
					$moduleFile = JPATH_ROOT.DS.'modules'.DS.$file.DS.JFile::stripExt($file).'.php';
					
					if (!is_file($configFile) || !is_file($moduleFile))
						break;
						
						
					$result = new stdClass;
					$result->type = 'module';
					$result->isAdmin = $isAdmin;
					$result->group = null;
					$result->extension = empty($parts[$i + 1]) ? '' : $parts[$i + 1];
					return $result;
					
				case 'templates':
					if (empty($parts[$i + 1]))
						break;
					
					if (!is_dir(JPATH_ROOT.DS.'templates'.DS.JFolder::makeSafe($parts[$i + 1])))
						break;
						
					$result = new stdClass;
					$result->type = 'template';
					$result->isAdmin = $isAdmin;
					$result->group = null;
					$result->extension = empty($parts[$i + 1]) ? '' : $parts[$i + 1];
					return $result;
			}
		}
		
		return false;
	}
	
	/**
	 * Get extension metadata by one of it's files
	 * @param $path Filename
	 */
	function getExtensionMetadata($path) {
		static $data = array();
		
		$info = $this->resolveExtension($path);
		
		if (!$info)
			return false;
			
		if (!isset($data[$info->type][(int)$info->group][$info->extension][(int)$info->isAdmin]))
		{	
			$result = false;
			
			switch ($info->type) {
				case 'component':
					$componentName = $info->extension;
					if (strpos($componentName, 'com_') === 0)
						$componentName = substr($componentName, 4);
					
					$result = $this->getComponentMetadata($componentName);
					break;
				case 'plugin':
					$result = $this->getPluginMetadata($info->extension, $info->group);
					break;	
				case 'module':
					$moduleName = $info->extension;
					if (strpos($moduleName, 'mod_') === false)
						$moduleName = 'mod_'.$moduleName;
					
					$result = $this->getModuleMetadata($moduleName, $info->isAdmin);
					break;
				case 'template':
					$result = $this->getTemplateMetadata($info->extension, $info->isAdmin);
					break;
				default:
					$this->setError(JText::_('Wrong extension type').': '.$info->type);
					$result = false;
			}
			
			// Reference to extension information
			$result->jd_extension_info = $info;
			
			$data[$info->type][(int)$info->group][$info->extension][(int)$info->isAdmin] = $result;
		}
		
		
		return $data[$info->type][(int)$info->group][$info->extension][(int)$info->isAdmin];
	}
	
	/**
	 * Gets component metadata
	 * @param $component Component name without prefix. For example - "resource"
	 */
	function getComponentMetadata($component) {
		if (empty($component))
			return false;
		
		$xmlFilesInDir = array();
		
		$folder = JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_'.$component;
		
		if (JFolder::exists($folder)) {
			$xmlFilesInDir = JFolder::files($folder, '.xml$');
		} else {
			$folder = JPATH_ROOT.DS.'components'.DS.'com_'.$component;
			if (JFolder::exists($folder)) {
				$xmlFilesInDir = JFolder::files($folder, '.xml$');
			} else {
				$xmlFilesInDir = null;
			}
		}
		
		if (count($xmlFilesInDir))
		{
			$row = new stdClass;
			
			foreach ($xmlFilesInDir as $xmlfile)
			{
				$data = JApplicationHelper::parseXMLInstallFile($folder.DS.$xmlfile);
				if ($data) {
					foreach($data as $key => $value) {
						$row->$key = $value;
					}
				}
			}
			
			return $row;
		}
		
		return false;
	}

	/**
	 * Get module metadata
	 * 
	 * @param $module Module name with prefix, ex. mod_latest
	 * @param $isAdmin Is admin module?
	 */
	function getModuleMetadata($module, $isAdmin) {
		// path to module directory
		if ($isAdmin)
			$moduleBaseDir = JPATH_ROOT.DS.'administrator'.DS."modules";
		else 
			$moduleBaseDir = JPATH_ROOT.DS."modules";

		// xml file for module
		$xmlfile = $moduleBaseDir.DS.$module.DS.$module.".xml";

		if (file_exists($xmlfile))
		{
			$data = JApplicationHelper::parseXMLInstallFile($xmlfile);
			$row = new stdClass;
			
			if ($data) {
				foreach($data as $key => $value) {
					$row->$key = $value;
				}
			}
			
			return $row;
		}
		
		return false;
	}
	
	/**
	 * Gets the plugin metadata
	 * @param $pluginName The plugin name
	 * @param $pluginGroup The plugin group
	 */
	function getPluginMetadata($pluginName, $pluginGroup) {
		// Get the plugin xml file
		$xmlfile = JPATH_ROOT.DS.'plugins'.DS.$pluginGroup.DS.$pluginName.".xml";

		if (file_exists($xmlfile)) {
			$data = JApplicationHelper::parseXMLInstallFile($xmlfile);
			
			$row = new stdClass;
			if ($data) {
				foreach($data as $key => $value)
					$row->$key = $value;
			}
			
			return $row;
		}
		
		return false;
	}
	
	function getTemplateMetadata($template, $isAdmin) {
		$dirName = $isAdmin ? JPATH_ROOT.'administrator'.DS.'templates'.DS.$template : JPATH_ROOT.DS.'templates'.DS.$template ;
		
		$xmlFilesInDir = JFolder::files($dirName,'.xml$');

		if (!count($xmlFilesInDir))
			return false;
		
		$row = new StdClass();
		foreach($xmlFilesInDir as $xmlfile)
		{
			$data = JApplicationHelper::parseXMLInstallFile($dirName . DS. $xmlfile);
			

			if ($data) {
				foreach($data as $key => $value)
					$row->$key = $value;
			}
		}
		
		return $row;
	}
}