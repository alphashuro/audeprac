<?php 
/**
 * $Id: menu.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined("_JEXEC") or die("Restricted Access"); ?>
 <style type="text/css">
        	div.jusermenu_head {
        		width: 150px;
        		border-bottom: 2px solid #414141;
        		padding:3px;
        	}
          div.ju_link{
        			text-align:left;
        			padding-top:3px;
        			padding-bottom:3px;
        			padding-left:12px;
        			background-color:aliceblue;
        	}
        	th.jdHeading
          {
            text-align:left;
            background-color:#F0f0F0;
            font-size:9pt;
          }
        </style>

     <div class="t">
      <div class="t">
        <div class="t"></div>
      </div>
    </div>
    <div class="m">
      <div class="jusermenu_head"><?php print JText::_( 'JDefender' );?></div>
      <div class="ju_link">
    		<a href="<?php echo JRoute::_('index.php?option=com_jdefender&controller=log&view=log&layout=log_groups'); ?>">
    			<img src="components/com_jdefender/images/log16.gif" align="absmiddle">
    			<?php print JText::_( 'Show Log' );?>
    		</a>
    	</div>
    	<div class="ju_link">
    		<a href="<?php echo JRoute::_('index.php?option=com_jdefender&controller=block'); ?>">
    			<img src="components/com_jdefender/images/block_list16.gif" align="absmiddle">
    			<?php print JText::_( 'Block List' );?>
    		</a>
    	</div>
    	<div class="ju_link">
    		<a href="<?php echo JRoute::_('index.php?option=com_jdefender&controller=configuration'); ?>">
    			<img src="templates/khepri/images/menu/icon-16-config.png" align="absmiddle">
    			<?php print JText::_( 'Configuration' );?>
    		</a>
    	</div>
    	<div class="ju_link">
    		<a href="<?php echo JRoute::_('index.php?option=com_jdefender&controller=scan'); ?>">
    			<img src="components/com_jdefender/images/toolbar/icon-16-scan.png" align="absmiddle">
    			<?php print JText::_( 'Scanner' );?>
    		</a>
    	</div>
    	<?php if ( 0 ): ?>
    	<div class="ju_link">
    		<img src="components/com_jdefender/images/dbrestore16.gif" align="absmiddle">
    		<a href="<?php echo JRoute::_('index2.php?option=com_jdefender&controller=restore'); ?>"><?php echo JText::_( 'Restore' );?></a>
    	</div>
    	<?php endif; ?>
    </div>
    <div class="b">
      <div class="b">
        <div class="b"></div>
      </div>
    </div>