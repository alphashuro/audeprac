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

jimport('joomla.application.component.controller');

class JDefenderControllerConfiguration extends JController
{
	function __construct()
	{
		parent::__construct();
		$this->registerTask('apply', 'save');
	}
	
	function display()
	{	
		JRequest::setVar('view', 'configuration');
		
		parent::display();
	}

	function save()
	{	 
		global $mainframe;

		$post = JRequest::get('post', 2);
	
		$model = & JModel::getInstance('Configuration', 'JDefenderModel');
		$model->save($post['params']);
		

		if (JRequest::getString('task') == 'apply')
			$this->setRedirect( 'index.php?option=com_jdefender&controller=configuration', JText::_('Configuration Saved'));
		else
			$this->setRedirect( 'index.php?option=com_jdefender', JText::_('Configuration Saved'));
	}
	
	function cancel() {
		$this->setRedirect( 'index.php?option=com_jdefender');
	}
}

?>