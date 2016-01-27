<?php
/**
 * $Id: default.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined('_JEXEC') or die ('Restricted Access');

?>
<style>

td.paramlist_key {
	white-space: nowrap;
}
</style>
<script>
	window.addEvent('domready', function() {
		var getHash = function() {
			var href = top.location.href;
			var pos = href.indexOf('#') + 1;
			return (pos) ? href.substr(pos) : '';
		};

		var hash = getHash();
		switch (hash) {
		case 'jd_config':
		case 'jd_config_scanner':
		case 'jd_alerts':
			$(hash).fireEvent('click');
		}		
	});
</script>
<form name='adminForm' id='adminForm' action='index.php' method='post'>
<?php echo $this->pane->startPane('jd_config_'); ?>

<?php echo $this->pane->startPanel(JText::_('Live Protection'), 'jd_config'); ?>
<table>
	<tr valign="top">
		<td>
			<?php echo $this->renderParamsGroup('flood_protection'); ?>
			<?php echo $this->renderParamsGroup('spam_protection'); ?>
		</td>
		<td>
			<?php echo $this->renderParamsGroup('injection_protection'); ?>
			
			<?php echo $this->slider->startPane('jd_inj'); ?>
				<?php echo $this->slider->startPanel(JText::_('SQL Injection Protection Settings'), 'jd_inj_sql'); ?>
					<?php echo $this->renderParamsGroup('SQL_injection_scanner', null, false); ?>
				<?php echo $this->slider->endPanel('jd_inj_sql'); ?>
				
				<?php echo $this->slider->startPanel(JText::_('PHP Injection Protection Settings'), 'jd_inj_php'); ?>
					<?php echo $this->renderParamsGroup('PHP_injection_scanner', null, false); ?>
				<?php echo $this->slider->endPanel('jd_inj_php'); ?>
			<?php echo $this->slider->endPane(); ?>
		</td>
	</tr>	
</table>
<?php echo $this->pane->endPanel(); ?>

<?php echo $this->pane->startPanel(JText::_('Scanner'), 'jd_config_scanner'); ?>
<table>
	<tr valign="top">
		<td>
			<?php //echo $this->renderParamsGroup('system_directories'); ?>
			<?php 
				echo $this->renderParamsGroup('scan_settings');
				
				$this->validatorParams['filesystem']->getParams('', 'php')
			?>
			<!--<fieldset>
				<legend><?php echo 'PHP'; ?></legend>
				<?php $this->renderValidatorParamGroup($this->validatorParams['filesystem'], 'php'); ?>
			</fieldset>
		--></td>
		<td>
		<?php echo $this->slider->startPane('jd_validators'); ?>
		
		<?php $count = 0; ?>
		<?php foreach ($this->validatorGroups as $group => $validators): ?>
			<?php if ($group != 'filesystem') continue; ?>
			<?php foreach ($validators as $validator) :?>
				<?php if ($this->validatorParams[$group]->getParams('', $validator)) : ?>
					<?php 
						$title = JString::ucwords(str_replace('_', ' ', JText::_($validator)));
						
						if ($title == 'Php')
							$title = 'PHP';
						
						if ($count == '2')
							echo '</td><td>';
					?>
					<fieldset>
						<legend><?php echo $title; ?></legend>
						<?php $this->renderValidatorParamGroup($this->validatorParams[$group], $validator); ?>
					</fieldset>
					
					<?php
						$count++; 
					?>
					
				<?php endif; ?>
			<?php endforeach; ?>
		<?php endforeach; ?>
		<?php echo $this->slider->endPane(); ?>
		</td>
	</tr>	
</table>
<?php echo $this->pane->endPanel(); ?>


<?php echo $this->pane->startPanel(JText::_('Alerts'), 'jd_alerts'); ?>
<table>
	<tr valign="top">
		<td>
			<?php echo $this->renderParamsGroup('jabber', $this->alertsParams); ?>
		</td>
	</tr>	
	<tr valign="top">
		<td>
			<?php echo $this->renderParamsGroup('mail', $this->alertsParams); ?>
		</td>
	</tr>	
</table>
<?php echo $this->pane->endPanel(); ?>

<?php echo $this->pane->startPanel(JText::_('Other Settings'), 'jd_config_other'); ?>
	<?php echo $this->renderParamsGroup('other_settings', null, false); ?>
<?php echo $this->pane->endPanel(); ?>


<?php echo $this->pane->endPane(); ?>
<input type="hidden" name="option" value="<?php echo $option;?>" />
<input	type="hidden" name="task" value="" />
<input type="hidden" name="controller" value="configuration" />
</form>
