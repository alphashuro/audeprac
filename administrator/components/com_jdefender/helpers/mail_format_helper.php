<?php
/**
 * $Id: mail_format_helper.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined ('_JEXEC') or die('Access Restricted');



class JD_Mail_Format_Helper extends JObject
{
	function __construct() {
		parent::__construct();
	}
	
	/**
	 * Formats mail fields according to record types
	 * @param $type
	 * @param $extension
	 * @param $data
	 * @param $defaultSubject
	 * @param $defaultBody
	 */
	function prepareMailFields($type, $extension, $data, $defaultSubject = '', $defaultBody = '') 
	{	
		if (empty($data['records']) || !count($data['records'])) {
			$this->setError(JText::_('There are no records'));
			return false;
		}
		
		$record = reset($data['records']);
		if (empty($record->type)) {
			$this->setError(JText::_('Record type is not defined'));
			return false;
		}
		
		$result = null;
		

		$header = '<p>'.
			JText::_('This message was automatically generated by').
			' <a href="http://www.mightyextensions.com/joomla-components/defender-security-firewall">MightyDefender</a></p>';
		
		if ($record->type == 'file_integrity_php_jexec')
		{
			$files = array();
			foreach ($data['records'] as $rec) {
				$files [] = str_replace(JPATH_ROOT, '', $rec->url);
			}
				
			$result = new stdClass;
			$result->title = JString::ucfirst(JText::_($type)). ': '. $extension.'. '
				.JText::_('Version'). ' '. $data['meta']->version
				.' by '.htmlentities($data['info'][0], ENT_QUOTES, 'UTF-8');
			
				
			$result->subject 	= empty($defaultSubject) ? JText::_('Feedback').'. '.$result->title : $defaultSubject;
			$result->body		= empty($defaultBody) ? JText::_('The following files are missing _JEXEC check: ')
				.'<br /><br />' .implode('<br />', $files) 
				: 
				$defaultBody;
				
			$result->body = $header .$result->body;
		}
		
		if ($record->type == 'file_integrity_php_bad_functions')
		{	
			$files = array();
			foreach ($data['records'] as $rec)
				$files [] = JText::_('File').': '.str_replace(JPATH_ROOT, '', $rec->url).': <br />'.nl2br($rec->issue).'<br />';
							
			$result = new stdClass;
			$result->title = JString::ucfirst(JText::_($type)). ': '. $extension.'. '
				.JText::_('Version'). ' '. $data['meta']->version
				.' by '.htmlentities($data['info'][0], ENT_QUOTES, 'UTF-8');
				
			$result->subject 	= empty($defaultSubject) ? JText::_('Feedback').'. '.$result->title : $defaultSubject;
			$result->body		= empty($defaultBody) ? JText::_('The following files contain functions, which may possibly cause security issues: ')
				.'<br /><br />' .implode('<br />', $files) 
				: 
				$defaultBody;
				
				
			$result->body = $header .$result->body;
		}
		
		return $result;
	}
}