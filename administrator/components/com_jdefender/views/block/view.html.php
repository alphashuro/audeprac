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

class JDefenderViewBlock extends JView
{
	function __construct($config = null) {
		parent::__construct($config);
		
		$doc = & JFactory::getDocument();
		$doc->addStyleSheet(JURI::base().'components/com_jdefender/css/toolbar.css');
	}
	
	function display($tpl = null)
	{
		if (JRequest::getVar('layout') == 'add')
		{
			$this->_form($tpl);
			return;
		}
		
		global $mainframe, $option;

		$document  = &JFactory::getDocument();
		$document->addStyleDeclaration(".icon-48-blocklist { background-image:url(components/com_jdefender/images/icon-48-block_list.gif); }");
		
		JToolBarHelper::title(JText::_("Block List"), 'blocklist.png');
		JToolBarHelper::addNew();
		JToolBarHelper::editList('add');
		JToolBarHelper::publishList();
		JToolBarHelper::unpublishList();
		JToolBarHelper::deleteList();

		$db		=& JFactory::getDBO();
		$uri	=& JFactory::getURI();
		$filter_order		= $mainframe->getUserStateFromRequest( $option.'.block.filter_order',		'filter_order',		'type');
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option.'.block.filter_order_Dir',	'filter_order_Dir',	'');
		$filter_type		= $mainframe->getUserStateFromRequest( $option.'.block.filter_type',		'type',				'');
		$published			= $mainframe->getUserStateFromRequest( $option.'.block.filter_published',	'published',		'');
		
		$lists['order_Dir'] = $filter_order_Dir;
		$lists['order'] = $filter_order;
		
		$model = & $this->getModel();
		$model->setState('filter_type', $filter_type);
		$model->setState('published', $published);
		
		$items		= $this->get( 'Data');
		$total		= $this->get( 'Total');
		$pagination = $this->get( 'Pagination' );
		$types		= $this->get('Types');
		
		if (!$total)
		{
			JError::raiseNotice(123, JText::_('There are no items to display'));
		}
		
		if (empty($items))
			$items = array();
		
		foreach ($items as $k => $v) {
			if ($v->type == 'ip')
				$v->type = 'IP Address';
			else if ($v->type == 'user' || $v->type == 'login')
				$v->type = 'User ID';
			else
				$v->type = JString::ucfirst($v->type);
			
			if (empty($v->reason))
				$v->reason = 'Not specified';
		}
		
		$options = array();
		$options [] = JHTML::_('select.option', '', '- '.JText::_('Select type').' -');
		foreach ($types as $t) {
			if ($t->type == 'ip')
				$title = JText::_('IP Address');
			else
				$title = JString::ucfirst(JText::_($t->type)); // user, referer.
			
			$options [] = JHTML::_('select.option', $t->type, $title);
		}
		$lists['type'] = JHTML::_('select.genericlist', $options, 'type', array('class' => "inputbox", 'onchange' => 'submitform();'), 'value', 'text', $filter_type);
		
		$options = array(
			JHTML::_('select.option', '', '- '.JText::_('Select state').' -'),
			JHTML::_('select.option', '0', JText::_('Unpublished')),
			JHTML::_('select.option', '1', JText::_('Published')),
		);
		
		$lists['published'] = JHTML::_('select.genericlist', $options, 'published', array('class' => 'inputbox', 'onchange' => 'submitform();'), 'value', 'text', $published);
		
		JHTML::_('behavior.tooltip');
		
		JD_Admin_Menu_Helper::decorate();
		
		$this->assignRef('lists',		$lists);
		$this->assignRef('items',		$items);
		$this->assignRef('pagination',	$pagination);
		$this->assignRef('request_url',	$uri->toString());


		parent::display($tpl);
	}

	function _form($tpl = null)
	{
		$document  = &JFactory::getDocument();
		$document->addStyleDeclaration(".icon-48-blocklist { background-image:url(components/com_jdefender/images/icon-48-block_list.gif); }");
		
		JToolBarHelper::title(JText::_('Add to Block List'), 'blocklist.png');
		JToolBarHelper::save();
		JToolBarHelper::cancel();
		
		$vars = JRequest::getVar('var', array(), 'default', 'array', JREQUEST_ALLOWRAW);
		
		if (empty($vars)) 
		{
			$cid = JRequest::getVar('cid', array());
			if (empty($cid))
			{
				$vars = array(
					1 => array()
				);
			}
			else {
				JArrayHelper::toInteger($cid);
				
				$model = & JModel::getInstance('Block', 'JDefenderModel');
				$model->setState('id', $cid);
				$vars = $model->getData();
				
				if (is_object(@$vars[ 0 ])) {
					foreach ($vars as $k => $v) {
						$vars[ $k ] = JArrayHelper::fromObject($v);
					}
				}
			}
		}
		
		JD_Admin_Menu_Helper::decorate();
		$this->assignRef('vars', $vars);
	
		parent::display($tpl);
	}
}