<?php
/**
 * $Id: report.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined ('_JEXEC') or die('Access Restricted');


$k = 0;
$i = 0;
?>
<form id="adminForm" name="adminForm" action="<?php echo $this->action; ?>" method="post">
	<table class="adminlist">
		<?php if ( !$this->metadata || (is_array($this->metadata) && !count($this->metadata)) ) : ?>
			<tr>
				<td>
					<h3><?php echo JText::_('Nothing to report'); ?></h3>
					<h3><?php echo JText::_('Configuration XML file not found or the files don\'t belong to an extension'); ?></h3>
				</td>
			</tr>
		<?php else: ?>
			<?php foreach ($this->metadata as $type => $data): ?>
				<?php foreach ($data as $extension => $d): ?>
					<?php
	
					$obj = $this->helper->prepareMailFields($type, $extension, $d, @$this->subject[$i], @$this->body[$i]);
					
					if (!$obj) {
						continue;
					}
						
					$titleText 	= $obj->title;
					$subject 	= $obj->subject;
					$body 		= $obj->body;
					?>
					<tr class="row<?php echo $k; $k = 1 - $k; ?>">
						<td colspan="2">
							<h3>
								<?php echo $titleText; ?>
							</h3>
						</td>
					</tr>
					<tr class="row<?php echo $k; $k = 1 - $k; ?>">
						<td>
							<?php echo JText::_('Subject'); ?>:
						</td>
						<td>
							<input type="text" size="70" name="subject[]" class="inputbox" value="<?php echo $this->escape($subject, ENT_QUOTES); ?>" />
						</td>
					</tr>
					<tr class="row<?php echo $k; $k = 1 - $k; ?>">
						<td>
							<?php echo JText::_('Email'); ?>:
						</td>
						<td>
							<input type="text" size="70" name="email[]" class="inputbox" value="<?php echo $this->escape($d['info'][1], ENT_QUOTES); ?>" />
						</td>
					</tr>
					<tr class="<?php echo $k; $k = 1 - $k; ?>">
						<td>
							<?php echo JText::_('Message'); ?>: 
						</td>
						<td>
							<?php echo $this->editor->display("body[]",  htmlspecialchars($body, ENT_QUOTES, 'UTF-8'), '100%', 300, '60', '20', false) ; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endforeach; ?>
		<?php endif; ?>
	</table>
	<input type="hidden" name="option" value="com_jdefender" />
	<input type="hidden" name="task" value="sendMail" />
	<input type="hidden" name="where" value="report" />
	<input type="hidden" name="extension" value="<?php echo $this->extension; ?>" />
	<input type="hidden" name="boxchecked" value="1" />
	<input type="hidden" name="ids" value="<?php echo $this->ids; ?>" />
	<input type="hidden" name="logType" value="<?php echo $this->logType; ?>" />
	<?php echo JHTML::_('form.token'); ?>
</form>