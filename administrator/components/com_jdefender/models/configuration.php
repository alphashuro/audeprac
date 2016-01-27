<?php
/**
 * $Id: configuration.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined("_JEXEC") or die("Restricted Access");
jimport('joomla.application.component.model');

class JDefenderModelConfiguration extends JModel
{
	function __construct()
	{
		parent::__construct();

		$this->_data = array();
		jimport ('joomla.filesystem.file');
		
		require_once JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_jdefender'.DS.'helpers'.DS.'vars.php';
	}
	
	function & getData($noXML = false)
	{
		static $data = array();
		
		if (empty($data[(int)$noXML]))
		{
			$xml = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_jdefender'.DS.'params.xml';
			if (!JFile::exists($xml))
				return JError::raiseError('404', JText::_('Defender params.xml not found'));
			
			if ($noXML)
				$xml = null;	
				
			$data[(int)$noXML] = new JParameter($this->getIni(), $xml);
		}
		return $data[(int)$noXML];
	}
	
	function & getIni() {
		static $ini = 0;
		
		if (empty($ini)) {		
			$basepath = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_jdefender';
			
			$p = new JParameter('');
			
			$conf = JD_Vars_Helper::getGroup('configuration');
			$p->bind($conf);
			
			$ini = $p->toString('ini');
		}
		
		return $ini;
	}

	function save($data)
	{
		foreach ($data as $k => $v) {
			JD_Vars_Helper::setVar($k, 'configuration', $v);
		}
		return true;
	}
}