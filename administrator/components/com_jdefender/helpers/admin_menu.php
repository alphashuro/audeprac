<?php
/**
 * $Id: admin_menu.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined ('_JEXEC') or die('Access Restricted');


class JD_Admin_Menu_Helper extends JObject
{
	function decorate() {
		$doc = & JFactory::getDocument();
		$doc->addStyleSheet(JURI::base().'components/com_jdefender/css/toolbar.css');
		
		// <a href="" class="active icon-16-log" style="margin-left: 5px; padding-left: 16px;"></a>
		
		
		$list = array(
			array("<span class=\"icon-16-log\" style=\"padding-left:15px\">Show log</span>", "index.php?option=com_jdefender&controller=log"),
  			array("<span class=\"icon-16-block_list\" style=\"padding-left:18px\">Block list</span>", "index.php?option=com_jdefender&controller=block"),
  			array("<span class=\"icon-16-config\" style=\"padding-left:20px\">Scanner</span>", "index.php?option=com_jdefender&controller=scan"),
  			array("<span class=\"icon-16-scan\" style=\"padding-left:20px\">Configurations</span>", "index.php?option=com_jdefender&controller=configuration")
		);
		
		foreach ($list as $k => $item)
		{
			if (strpos( @$_SERVER['QUERY_STRING'], $item[1] ) !== false )
				$list[ $k ][ 2 ] = 1;
			else
				$list[ $k ][ 2 ] = 0;
		}
		
		
		$menu = & JToolBar::getInstance('submenu');
		
		$menu->_bar = $list;
	}
}
