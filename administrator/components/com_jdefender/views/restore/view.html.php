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
jimport ( 'joomla.application.component.view' );

class JDefenderViewRestore extends JView {
	function display($tpl = null) {
		global $mainframe, $option;
		if ($this->getLayout () == 'form') {
			$this->_displayForm ( $tpl );
			return;
		}
		//JToolBarHelper::title( JText::_( 'Restoration' ), 'generic.png' );
		$html = "<div class=\"header icon-48-generic.png\" style='background-image:url(components/com_jdefender/images/dbrestore.png)'>\n";
		$html .= JText::_ ( 'Restoration' );
		$html .= "\n</div>\n";
		$mainframe->set ( 'JComponentTitle', $html );
		JToolBarHelper::deleteList ();
		JToolBarHelper::addNewX ();
		$html = "<a href=\"javascript:void(0);\" onclick=\"javascript:" . "document.adminForm; submitbutton('restore');\" class=\"toolbar\">";
		$html .= "<span style='background-image:url(components/com_jdefender/images/dbrestore32.gif);' type=\"Standard\">";
		$html .= "</span>";
		$html .= JText::_ ( 'Restore' );
		$html .= "</a>";
		$bar = & JToolBar::getInstance ();
		$bar->appendButton ( 'Custom', $html, 'close' );
		$db = & JFactory::getDBO ();
		$uri = & JFactory::getURI ();
		$items = & $this->get ( 'Data' );
		
		$pagination = & $this->get ( 'Pagination' );
		$this->assignRef ( 'pagination', $pagination );
		$this->assignRef ( 'restorations', $items );
		parent::display ( $tpl );
	
	}
	function _displayForm($tpl) {
		global $mainframe, $option;
		$html = "<a href=\"javascript:void(0);\" onclick=\"javascript:" . "document.adminForm; submitbutton('create');\" class=\"toolbar\">";
		$html .= "<span style='background-image:url(components/com_jdefender/images/dbrestore32.gif);' type=\"Standard\">";
		$html .= "</span>";
		$html .= JText::_ ( 'Create' );
		$html .= "</a>";
		$bar = & JToolBar::getInstance ();
		$bar->appendButton ( 'Custom', $html, 'close' );
		JToolBarHelper::back ();
		$tables = &$this->get ( 'TablesInfo' );
		$this->assignRef ( 'tablesInfo', $tables );
		//print "<pre>";
		//print_r($tables);
		//print "</pre>";
		parent::display ( $tpl );
	
	}

}

?>