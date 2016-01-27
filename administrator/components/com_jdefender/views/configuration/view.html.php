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
defined('_JEXEC') or die('Restricted Access');
jimport('joomla.application.component.view');

class JDefenderViewConfiguration extends JView
{
	function display($tpl = null)
	{
		require_once JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'configuration.php';
		
		JToolBarHelper::title ( JText::_ ('Configurations'), 'config' );
		JToolBarHelper::save ();
		JToolBarHelper::apply ();
		JToolBarHelper::cancel ( 'cancel', 'Close' );
		
		
		JHTML::_( 'behavior.tooltip' );
		JHTML::_( 'behavior.switcher' );
		jimport ( 'joomla.html.pane' );
		
		
		$helper 	= & JDefenderConfigurationHelper::getInstance();
		$options 	= & $this->get('Data');
		
		$pane 		= & JPane::getInstance('tabs');
		
		$validatorParams = & $helper->getValidatorParams(true);
		$validatorGroups = & $helper->getValidatorGroups();

		$alertsParams	= & $helper->getAlertsParams();
		
		$slider = & JPane::getInstance('sliders');
		
		$this->assignRef('validatorParams', $validatorParams);
		$this->assignRef('alertsParams', 	$alertsParams);
		$this->assignRef('validatorGroups', $validatorGroups);
		$this->assignRef('parameters', 		$options);
		$this->assignRef('pane', 			$pane);
		$this->assignRef('slider',			$slider);

		JD_Admin_Menu_Helper::decorate();
		
		parent::display($tpl);
	}
	
	function renderParamsGroup($group_name, $params = null, $fieldset = true) {
		?>
		<?php if ($fieldset) : ?>
		<fieldset class="adminform"><legend><?php echo JText::_( ucwords(str_replace('_', ' ', $group_name)) );?></legend>
		<?php endif; ?>
		
		<?php echo $params ? $params->render( 'params', $group_name ) : $this->parameters->render( 'params', $group_name ); ?>
		<?php if ($fieldset) : ?>
		</fieldset>
		<?php endif; ?>
		<?php
	}
	
	function renderValidatorParamGroup(&$param, $group_name) {
		if ($param->getParams('', $group_name) === false)
			return;
		echo $param->render ('params', $group_name);
	}
}