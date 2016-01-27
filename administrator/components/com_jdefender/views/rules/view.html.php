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
defined ('_JEXEC') or die('Access Restricted');

jimport ('joomla.application.component.view');

class JDefenderViewRules extends JView
{
	function display($tpl = null) {
		global $mainframe, $option;
		

		$document  = &JFactory::getDocument();
		
		$document->addStyleSheet(JURI::base().'components/com_jdefender/css/main.css');
		$document->addStyleSheet(JURI::base().'components/com_jdefender/css/toolbar.css');
		
		JToolBarHelper::title(JText::_("Block List"), 'blocklist.png');
		JToolBarHelper::addNew();
		JToolBarHelper::editList('add');
		JToolBarHelper::publishList();
		JToolBarHelper::unpublishList();
		JToolBarHelper::deleteList();
		
		$filter_order		= $mainframe->getUserStateFromRequest( $option.'.rules.filter_order',		'filter_order',		'type');
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option.'.rules.filter_order_Dir',	'filter_order_Dir',	'');
		$type				= $mainframe->getUserStateFromRequest( $option.'.rules.filter_type',		'type',				'');
		$published			= $mainframe->getUserStateFromRequest( $option.'.rules.filter_published',	'published',		null);
		$limitstart			= $mainframe->getUserStateFromRequest( $option.'.rules.filter_limitstart',	'limitstart',		'');
		$limit				= $mainframe->getUserStateFromRequest( $option.'.rules.filter_limit',		'limit',		'');
		$state				= $mainframe->getUserStateFromRequest( $option.'.rules.filter_state',		'state',		null);
		$search				= $mainframe->getUserStateFromRequest( $option.'.rules.filter_search',		'search',		'');
		
		$lists = array();
		$lists['order_Dir'] = $filter_order_Dir;
		$lists['order'] 	= $filter_order;
		$lists['state'] 	= $state;
		$lists['search'] 	= $search;
		$lists['type'] 		= $type;
		$lists['published']	= $published;
		
		$model = & JModel::getInstance('Rule', 'JDefenderModel');
		
		$model->setState('state', 		$state);
		$model->setState('type', 		$type);
		$model->setState('search', 		$search);
		$model->setState('order', 		$filter_order);
		$model->setState('orderDir', 	$filter_order_Dir);
		$model->setState('published', 	$published);
		
		if ($limit) {
			$model->setState('limit', $limit);
			$model->setState('limitstart', $limitstart);
		}
		
		
		$filters = $this->_getFilters($lists);
		
		$rules 		= $model->getData();
		$pagination = $model->getPagination();
		
		
		
		
		
		foreach ($rules as $k => $v) {
			switch ($v->origin) {
				case 0:
					$rules[ $k ]->origin = JText::_('Mighty Defender');
					break;
				case 1:
					$rules[ $k ]->origin = JText::_('Custom');
					break;
				case 2:
					$rules[ $k ]->origin = JText::_('Third party');
					break;
			}
			
			$rules[ $k ]->type = $this->_beautifyRuleType($rules[ $k ]->type);
			
			
			$res = JD_Log_Helper::readableLogType($v->family);
			$rules[ $k ]->familyTitle = $res->title;
			 
			$rules[ $k ]->actionTitle = JHTML::link(
				'index.php?option=com_jdefender&controller=rules&view=rules&layout=rule&id='.$v->id.'&tmpl=component',
				JD_Log_Helper::beautifyString($v->action, true),
				array('rel' => '{handler: \'iframe\', size: {x: 800, y: 600}}', 'class' => 'modal')
			);
			 
			$rules[ $k ]->editLink = JHTML::link(
				'index.php?option=com_jdefender&controller=rules&view=rules&layout=form&id='.$v->id,
				JHTML::image(JURI::base().'components/com_jdefender/images/toolbar/icon-16-edit.png', JText::_('Edit'))
			); 
			$rules[ $k ]->viewLink = JHTML::link(
				'index.php?option=com_jdefender&controller=rules&view=rules&layout=rule&id='.$v->id.'&tmpl=component',
				JHTML::image(JURI::base().'components/com_jdefender/images/toolbar/icon-16-preview.png', JText::_('View')),
				array('rel' => '{handler: \'iframe\', size: {x: 800, y: 600}}', 'class' => 'modal')
			);
		}
		
		$this->assignRef('items', $rules);
		$this->assignRef('lists', $lists);
		$this->assignRef('pagination', $pagination);
		
		JHTML::_('behavior.modal');
		JHTML::_('behavior.tooltip');
		JD_Admin_Menu_Helper::decorate();
		
		return parent::display($tpl);
	}
	
	function _getFilters($lists) {
		$result = array();
		
		$model = & JModel::getInstance('Rule', 'JDefenderModel');
		
		$options = array();
		$types = $model->getTypes();
		foreach ($types as $type)
			$options [] = JHTML::_('select.option', $type, $this->_beautifyRuleType($type));
		
		$typesSelect = JHTML::_('select.genericlist', $options, 'type', null, 'key', 'value', $lists['type']);
		
		$options = array(
			JHTML::_('select.option', '', ' -'.JText::_('Select state').'- '),
			JHTML::_('select.option', 0, JText::_('Unpublished')),
			JHTML::_('select.option', 1, JText::_('Published')),
		);
		
		$publishSelect = JHTML::_('select.genericlist', $options, 'published', null, 'key', 'value', $lists);
		
		return $result;
	}
	
	function _beautifyRuleType($type) {
		switch ($type) {
			case 'preg':
				return JText::_('Regular Expression');
			case 'equality':
				return JText::_('Comparision');
		}
		return false;
	}
}