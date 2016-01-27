<?php
/**
 * $Id: view.html.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined("_JEXEC") or die("Restricted Access");

jimport('joomla.application.component.view');


class JDefenderViewLog extends JView
{
	function __construct($config = null) {
		parent::__construct($config);
		
		require_once JPATH_COMPONENT.DS.'helpers'.DS.'options_toolbar_helper.php';
	}
	
	function display($tpl = null) {
		global $mainframe, $option;

		$uri 	= & JFactory::getURI();
		
		$document = & JFactory::getDocument();
		$document->addStyleSheet(JURI::root().'administrator/components/com_jdefender/css/toolbar.css');
		
		if (JRequest::getString('layout') == 'log_groups')
			return $this->showLogGroups();
			
		if (JRequest::getString('layout') == 'options')
			return $this->showOptions();
			
		if (JRequest::getString('layout') == 'report')
			return $this->reportDeveloper();
		
		JToolBarHelper::title(JText::_('Detailed log'), 'log');
		JToolBarHelper::unpublishList('ignore', JText::_('Ignore'));
		JToolBarHelper::deleteList();

		
		// -------
		$type = JRequest::getVar('cid');
		$type = $type[ 0 ];
		
		// Set title for specific log group
		if ($type)
		{
			$dummy = new stdClass;
			$dummy->type = $type;
			
			$this->_decorateLogGroup($dummy);
			
			JToolBarHelper::title(JText::_('Detailed log').' - '.JText::_($dummy->title), 'log');
		}
		
		$filter_order		= $mainframe->getUserStateFromRequest( $option.'.'.$type.'.log.filter_order',		'filter_order',		'ctime' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option.'.'.$type.'.log.filter_order_Dir',	'filter_order_Dir',	'');
		$limitstart			= $mainframe->getUserStateFromRequest( $option.'.'.$type.'.log.limitstart',			'limitstart',		'0' );
		$limit				= $mainframe->getUserStateFromRequest( $option.'.'.$type.'.log.limit',				'limit',			'15');
		$state				= $mainframe->getUserStateFromRequest( $option.'.'.$type.'.log.filter_state',		'filter_state',		'');
		$search				= $mainframe->getUserStateFromRequest( $option.'.'.$type.'.log.filter_search',		'filter_search',	'');
		$extension			= $mainframe->getUserStateFromRequest( $option.'.'.$type.'.log.filter_extension',	'filter_extension',	'');
		
		$lists['order_Dir'] 	= $filter_order_Dir;
		$lists['order']    		= $filter_order;
		$lists['limitstart'] 	= $filter_order_Dir;
		$lists['limit']    		= $filter_order;
		$lists['state']    		= $state;
		$lists['search']   		= JString::strtolower($search);
		$lists['extension']   	= JString::strtolower($extension);
		
		$model = & JModel::getInstance('Log', 'JDefenderModel');
		
		$model->setState('type', 				$type);
		
		$model->setState('filter_order', 		$filter_order);
		$model->setState('filter_order_Dir', 	$filter_order_Dir);
		
		if ($limit) {
			$model->setState('limit', $limit);
			$model->setState('limitstart', $limitstart);
		}
		
		$model->setState('filter_state',		$state);
		$model->setState('filter_search',		$search);
		$model->setState('filter_extension',	$extension);
		
		$states = $model->getStatusesForType($type);
		if ($state)
		{
			// Unset the state if it not exists
			$stateExists = false;
			foreach ($states as $s) {
				if ($s->status == $state) {
					$stateExists = true;
					break;
				}
			}
			
			
			if ( ! $stateExists) {
				$model->setState('filter_state', null);
				$state = null;
			}
		}
		
		$items		= & $model->getData();
		$total		= & $model->getTotal();
		$pagination = & $model->getPagination();
		
		
		// toolbar
		
		$somethingToReport = true;
		if ($extension) 
		{
			$model->_state = new JObject();
			$model->setState('filter_extension', $extension);
			$model->setState('type', $type);
			$model->setState('filter_state', array('bad_functions', 'insecure'));
			
			$somethingToReport = $model->getTotal();
		}
		
		$toolbarHelper = & ME_Defender_Options_Toolbar_Helper::getInstance();
		if ($somethingToReport)
			$toolbarHelper->makeButtons($type, $extension);
		else
			$toolbarHelper->makeButtons($type);
		// override the toolbar.
		$nativeToolbar = & JToolBar::getInstance();
		$nativeToolbar = $toolbarHelper->getToolbar();
		
		if (!$total) 
		{
			JError::raiseNotice('DEF_NO_LOGS', JText::_('Logs are empty'));
		}

		// Prepare the items
		for ($i = 0, $c = count($items); $i < $c; $i++) {
			$this->_decorateLogGroup($items[ $i ]);
			$this->_decorateLogRecord($items[ $i ]);
			
			$truncated = false;
			
			if ($items[ $i ]->ip)
			{
				// truncate the string
				$items[ $i ]->url = $items[ $i ]->ip.' - '.$items[ $i ]->url;
				if (JString::strlen($items[ $i ]->url) > 80) {
					$items[ $i ]->url = JString::substr($items[ $i ]->url, 0, 80);
					$truncated = true;
				}
			}

			$items[ $i ]->source = JHTML::link('index.php?option=com_jdefender&tmpl=component&task=showOptions&id='.$items[ $i ]->id, $items[ $i ]->url, array('rel' => '{handler: \'iframe\', size: {x: 800, y: 600}}', 'class' => 'modal'));
			
			if ($truncated)
				$items[ $i ]->source .= ' ... '.JText::_('Truncated');
			if ( ! $items[ $i ]->blocked_ip)
				$items[ $i ]->blocked_ip = JD_Block_Helper::isIPBlocked($items[ $i ]->ip, true);
				
			$items[ $i ]->ctime = JD_Log_Helper::formatDate($items[ $i ]->ctime);
		}
		
		
				
		$logGroups = & $model->getLogGroups();
		if (empty($logGroups))
			$logGroups = array();
		
		$uri = & JURI::getInstance();
		$url = $uri->toString();
		
		$options = array();
		$options [] = JHTML::_('select.option', '', '- '.JText::_('Change log section').' -');
		
		// Preformat the log groups
		for ($i = 0, $c = count($logGroups); $i < $c; $i++) 
		{
			// The controls for log groups
			$logGroups[ $i ]->id 		= $logGroups[ $i ]->type; 
			$logGroups[ $i ]->editLink	= 'index.php?option=com_jdefender&controller=log&task=showLog&cid[]='.$logGroups[ $i ]->type;
			
			$this->_decorateLogGroup($logGroups[ $i ]);
			
			$options [] = JHTML::_('select.option', $logGroups[ $i ]->type, $logGroups[ $i ]->title);
		}
		
		$lists['quickjump'] = JHTML::_('select.genericlist', $options, 'quickjump', array('onchange' => 'window.location.href=\'index.php?option=com_jdefender&controller=log&task=showLog&cid[]=\' + this.value'));
		
		
		
		$options = array();
		$options [] = JHTML::_('select.option', '', '- '.JText::_('Select status').' -');
		foreach ($states as $k => $v) {
			$options [] = JHTML::_('select.option', $v->status, JString::ucfirst(str_replace('_', ' ', $v->status)));
		}
		
		$lists['state'] = JHTML::_('select.genericlist', $options, 'filter_state', array('class' => 'inputbox', 'onchange' => 'submitform();'), 'value', 'text', $state);
		
		$loggedExtensions = $model->getLoggedExtensions();
		
		$options = array();
		$options [] = JHTML::_('select.option', '', '- '.JText::_('Select extension').' -');
		
		foreach ($loggedExtensions as $l) {
			$v = $l->extension;
			if (empty($v))
				continue;
				
			$ext = explode('::', $v);
			if (count($ext) != 3)
				continue;
			
			/// type and extension must be set.
			if (empty($ext[0]) || empty($ext[2]))
				continue;
				
			$name = JString::ucwords(JText::_($ext[0])).': '.JText::_($ext[2]);
			if ($ext[1])
				$name .= ' '.JText::_('Group').': '.$ext[ 1 ];

			$options [] = JHTML::_('select.option', $v, $name);
		}
		
		$lists['extension'] = JHTML::_('select.genericlist', $options, 'filter_extension', array('class' => 'inputbox', 'onchange' => 'submitform();'), 'value', 'text', $extension);		
		
		$types = JRequest::getVar('types', array());
		
		
		if ( in_array($type, array('blocked_users_ips', 'php_injection', 'sql_injection')) )
			$this->assign('showTotal', true);
		else
			$this->assign('showTotal', false);
		

		$this->assignRef('lists',		$lists);
		$this->assignRef('items',		$items);
		$this->assignRef('total',		$total);
		$this->assignRef('pagination',	$pagination);
		$this->assignRef('request_url',	$uri->toString());
		$this->assignRef('types', 		$types);
		
		$this->assignRef('logType',		$type);
		
		JHTML::_('behavior.modal');
		JHTML::_('behavior.tooltip');
		
		JD_Admin_Menu_Helper::decorate();
		
		parent::display($tpl);
	}
	
	function showOptions() {
		global $mainframe;
		
		require_once JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_jdefender'.DS.'lib'.DS.'utils'.DS.'jd_extension_resolver.php';
		
		$id 		= JRequest::getInt('id');
		$fsModel 	= & JModel::getInstance('Filesystem', 'JDefenderModel');
		$log 		= & JTable::getInstance('Log', 'Table');
		
		$document = & JFactory::getDocument();
		$document->addStyleSheet('templates/system/css/system.css');
		$document->addStyleSheet('templates/khepri/css/template.css');
		
		// Toolbar
		
		$toolbarHelper = & ME_Defender_Options_Toolbar_Helper::getInstance();
		
		if ($showThatItemWasDeleted = JRequest::getInt('deleted')) {
			$toolbar = & $toolbarHelper->getToolbar();
			
			$toolbar->closeRefresh('Close');
			
			$this->assign('deleted', true);
			$this->assign('toolbar', $toolbar);
			
			return parent::display();
		}
		
		if (!$log->load($id)) {
			return $mainframe->redirect('index.php?option=com_jdefender', JText::_('The log record does not exist'));
		}
		
		// Toolbar
		$extension = false;
		
		if (in_array($log->type, array('file_integrity_php_bad_functions', 'file_integrity_php_jexec')) 
			&& $log->extension && in_array($log->status, array('insecure', 'bad_functions')) ) 
		{
			$resolver = new JD_Extension_Resolver();
			
			if ($resolver->getExtensionMetadata($log->url))
				$extension = $log->extension;
		}
		
		if ($log->type == 'blocked_users_ips')
			$oldRefresh = JRequest::setVar('refresh', 1);
			
		$toolbarHelper->makeButtons($log->type, $extension, $log->status, $log->url);
		
		if ($log->type == 'blocked_users_ips')
			JRequest::setVar('refresh', $oldRefresh);
		
		// Load the log reader
		JD_Log_Reader::loadReaders();
		$reader = & JD_Log_Reader::getInstance($log->type);
		
		if (empty($reader))
			JError::raiseError(500, JText::_('Log reader not found for type').': '.$log->type);
		
		$reader->setRecord($log);
			
		
		$isUrl = JString::substr($log->url, 0, 7) == 'http://';
		
		// The last scan information from filesystem table 
		$scanInfo = false;
		if ($log->url && !$isUrl) {
			$scanInfo = $fsModel->getFiles($log->url);
		
			$scanInfo = @reset($scanInfo);

			if (empty($scanInfo)) {
				$table = & JTable::getInstance('Filesystem', 'Table');
				$table->loadFromFile($log->url);
				$scanInfo = $table;
			}
		}
		
		$reader->setCurrentState($scanInfo);
		
		// Get the data from log reader
		$tables	= $reader->getTables();
		
		
		// Labels:
		$labels = new stdClass;
		if ($isUrl)
			$labels->source = JText::_('URL');
		elseif (!empty($log->url)) {
			// Assume that it's file.
			$labels->source = JText::_("File");
			
			if (is_dir($log->url))
				$labels->source = JText::_("Directory");
			else {
				jimport ('joomla.filesystem.file');
				if (!JFile::getExt($log->url))
					$labels->source = JText::_("Directory");
			}
		}
		
		$labels->statusTitle = JText::_('Status');
		
		if ($log->type == 'blocked_users_ips')
			$labels->statusTitle = JText::_('Action Applied');
		
		$dummy = new stdClass;
		$dummy->type = $log->type;
		$this->_decorateLogGroup($dummy);
		
		$labels->title = $dummy->title;
		
		JHTML::_('behavior.tooltip');
		
		
		$uri = JURI::getInstance();
		$this->assign('url', $uri->toString()); 

		$this->assign('toolbar',	$toolbarHelper->getToolbar());
		$this->assign('logRecord', 	$log);
		$this->assign('labels', 	$labels);
		$this->assign('scanInfo', 	$scanInfo);
		
		$this->assign('tables',	$tables);
		
		return parent::display();
	}
	
	function showLogGroups() {
		global $mainframe, $option;
		
		JToolBarHelper::title(JText::_('The log'), 'log');
		JToolBarHelper::editList('showLog', JText::_('Show log'));
		JToolBarHelper::deleteList(JText::_('Are you sure?'), 'removeGroup');
		
		
		
		$filter_order		= $mainframe->getUserStateFromRequest( $option.'.log_group.filter_order',		'filter_order',		'type' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option.'.log_group.filter_order_Dir',	'filter_order_Dir',	'');
		
		$lists['order_Dir'] = $filter_order_Dir;
		$lists['order']    	= $filter_order;

			
		$model = & $this->getModel();
		
		$logGroups 		= & $model->getLogGroups();
		$logTotalGroups = $model->getTotalLogGroups();
		
		$uri = & JURI::getInstance();
		$url = $uri->toString();
		
		if (!$logTotalGroups) 
		{
			JError::raiseNotice(123, JText::_('Logs are empty'));
		}
		
		// Preformat the log groups
		for ($i = 0, $c = count($logGroups); $i < $c; $i++) 
		{
			// The controls for log groups
			$logGroups[ $i ]->id 		= $logGroups[ $i ]->type; 
			$logGroups[ $i ]->editLink	= JRoute::_('index.php?option=com_jdefender&controller=log&task=showLog&cid[]='.$logGroups[ $i ]->type);

			
			$this->_decorateLogGroup($logGroups[ $i ]);
			
			$logGroups[ $i ]->ctime = JD_Log_Helper::formatDate($logGroups[ $i ]->ctime);
		}
		
		JHTML::_('behavior.modal');
		JHTML::_('behavior.tooltip');
		JD_Admin_Menu_Helper::decorate();
		
		$this->assignRef('items', $logGroups);
		$this->assignRef('total', $logTotalGroups);
		$this->assignRef('lists', $lists);
		parent::display();
	}
	
	function reportDeveloper()
	{
		require_once JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_jdefender'.DS.'helpers'.DS.'mail_format_helper.php';
		
		$logId 		= JRequest::getInt('id');
		$extension	= JRequest::getString('filter_extension');
		
		$types 	= JRequest::getVar('types', array(), 'default', 'array');
		$type 	= JRequest::getCmd('type');
		
		$logType = isset($types[0]) ? $types[ 0 ] : $type;
		
		$title = JText::_('Inform Developer');
		JToolBarHelper::title($title, 'log');
		JToolBarHelper::custom('sendMail', 'send', '', JText::_('Send'));
		JToolBarHelper::cancel('cancel');
		
			
		$model 		= & JModel::getInstance('Log', 'JDefenderModel');
		
		$model->setState('filter_extension', $extension);
		$model->setState('type', $logType);
		$model->setState('filter_state', array('bad_functions', 'insecure'));
		$ids = $model->getIds();
		
		
		$model->setState('filter_extension', null);
		
		$metadata 	= $model->getExtensionMetadataForLogs($ids);
				
				
		$editor 	= & JFactory::getEditor();
		$subject	= JRequest::getVar('subject', array(), 'default', JREQUEST_ALLOWRAW);
		$body		= JRequest::getVar('body', array(), 'default', JREQUEST_ALLOWRAW);
		
		
		
		JHTML::_('behavior.mootools');
		JD_Admin_Menu_Helper::decorate();
		
		$helper = new JD_Mail_Format_Helper();
		
		JError::raiseNotice(404, JText::_('Please, do not mail developers if you are not convinced about the problem. Frequent messages may annoy them.'));
		
		$this->assign('title', 			JText::_('Report'));
		$this->assign('action', 		JURI::base());
		$this->assignRef('metadata', 	$metadata);
		$this->assignRef('editor', 		$editor);
		$this->assignRef('helper', 		$helper);
		$this->assignRef('body', 		$body);
		$this->assignRef('subject', 	$subject);
		$this->assignRef('extension', 	$extension);
		
		$this->assignRef('logType', 	$logType);
		$this->assign('ids', implode(',', $ids));
		
		
		return parent::display();
	}
	
	
	function _decorateLogGroup(&$logRecord) {
		require_once JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'log.php';
		
		$r = JD_Log_Helper::readableLogType($logRecord->type);
		
		$logRecord->title = $r->title;
		$logRecord->description = $r->description;
		
		if ($logRecord->type == 'file_integrity_php_bad_functions' && !empty($logRecord->issue))
			$logRecord->issue = JText::_('Occurences').': '.count(explode("\n", $logRecord->issue));
	}
	
	function _decorateLogRecord(&$logRecord) {
		switch ($logRecord->type) {
			case 'file_integrity_php_bad_functions':
				$logRecord->issue = JText::_('Occurences').': '.count(explode("\n", trim($logRecord->issue, "\n")));
				break;
			
			case 'blocked_users_ips':
				if ($logRecord->issue == 'access from blocked ip')
					$logRecord->issue = 'Access From Blocked IP';
				
				$logRecord->issue = JString::ucwords(str_replace('_', ' ', $logRecord->issue));
				break;
			case 'file_integrity_php_jexec':
				if ($logRecord->issue == 'No _JEXEC check') {
					$logRecord->issue = 'Directly executable file. Missing _JEXEC check in code';
				}
		}
		
		$logRecord->status = $this->_beautifyWord($logRecord->status);
	}
	
	function _beautifyWord($word) {
		if ($word == 'hash_md')
			$word = 'md5 hash';
			
		$pos = JString::strpos($word, '|');
		if ($pos !== false) {
			return JString::substr($word, $pos + 1);
		}
		
		return JString::ucwords(str_replace('_', ' ', $word));
	}
}
