<?php
/**
 * $Id: admin.jdefender.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined('_JEXEC') or die('Restricted Access');
/*
$license = JPATH_COMPONENT_ADMINISTRATOR.DS.'license.txt';

if ( ! is_file($license) )
{
	if (is_file(JPATH_ADMINISTRATOR.DS.'defined'.DS.'com_jslm'.DS.'jslm.php'))
	{
		$mainframe->redirect('index.php?option=com_jslm&cmt=com_jdefender');
	}
	else
	{
		JError::raiseWarning(403, JText::_('Please install Mighty Assistant.').' <a target="_blank" href="http://www.mightyextensions.com/download-mighty-assistant">'.JText::_('Download').'</a>');
		$mainframe->redirect('index.php');
	}
}*/

require_once JPATH_COMPONENT_ADMINISTRATOR.DS.'lib'.DS.'include.php';

jimport('joomla.application.component.model');

JModel::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.DS.'models');
JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.DS.'tables');

$config = JD_Vars_Helper::getGroup('configuration');

if (empty($config) && JRequest::getCmd('controller') != 'configuration') {
	JRequest::setVar('controller', 'configuration');
	JRequest::setVar('task', 'display');
	JRequest::setVar('view', 'configuration');
	JRequest::setVar('layout', 'default');
	
	JError::raiseNotice(123, JText::_('Please, save the configurations'));
}

if ($controller = JRequest::getWord('controller'))
{
  $path = JPATH_COMPONENT . DS . 'controllers' . DS . $controller . '.php';
  if (file_exists($path))
  {
    require_once ($path);
    $classname = 'JDefenderController'.ucfirst($controller);
  }

} else {
    $path = JPATH_COMPONENT.DS.'controllers'.DS.'log.php';
    require_once ($path);
    $classname = 'JDefenderControllerLog';
}

require_once (JPATH_COMPONENT_ADMINISTRATOR . DS . 'xajax.php');
$controller = new $classname();
// Perform the Request task
$controller->execute(JRequest::getVar('task'));
// Redirect if set by the controller
$controller->redirect();
?>
<style type="text/css">
 #adesk-button{
 position: fixed;
 right: 0;
 top: 50%;
 background: url(https://s3.amazonaws.com/adws/widgets/imgs/fs-r-joomla.png) no-repeat;
 height: 250px;
 width: 27px
}
</style>
    <a href="javascript:adesk_stak.init.call();" id="adesk-button"></a>

          <div id="adesk-widget" style="display:none;">

            <div id="wd-overlay">

              <div class="info-box"><a class="close" href="javascript:javascript:adesk_stak.closePop();"></a></div>

            </div>

            <div id="wd-expose"></div>

          </div>
          
     <script type="text/javascript">

      adesk_option = {

        prod: 'mighty-defender-joomla-security-firewall-component',

                    user: 'mightyextensions',

                    lang:'en'

      }

    </script>

    <script type="text/javascript" src="https://s3.amazonaws.com/adws/widgets/feed/widget.js"></script>