<?php
/**
 * $Id: options.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined ('_JEXEC') or die("Restricted Access");

?>
<script>
	function toggleLockImage(imgId) {
		var i = document.getElementById(imgId);

		if (!i)
			return;
		
		if (i.src == '<?php print JURI::base(); ?>components/com_jdefender/images/locked.gif')
			i.src = '<?php print JURI::base(); ?>components/com_jdefender/images/unlocked.gif';
		else
			i.src = '<?php print JURI::base(); ?>components/com_jdefender/images/locked.gif';
	}
</script>
<form action="<?php echo $this->url; ?>" method="post" name="adminForm" id="adminForm">
<div>
<div class="h_green" id="border-top">
	<div>
		<div></div>
	</div>
</div>
<div id="content-box">
	<div class="border">
		<div class="padding">
			<div id="element-box">
				<div class="t">
					<div class="t">
						<div class="t"></div>
					</div>
				</div>
				<div class="m">
					<div>
						<h3 style="float:left">
							<?php echo $this->labels->title; ?>
						</h3>
						<?php echo $this->toolbar->render();?>
					</div>
					<div style="clear:both;height:0px"></div>
					<?php if (empty($this->deleted)) : ?>
					<div>
						<h3 style="word-wrap: break-word">
							<?php if (!empty($this->logRecord->url)) : ?>
								<?php echo $this->labels->source; ?>: <?php echo $this->logRecord->url; ?><?php if (!empty($this->logRecord->status)) echo ','; ?>
							<?php endif; ?>
							<?php if (!empty($this->logRecord->status)) : ?>
								<?php echo $this->labels->statusTitle, ': ', $this->_beautifyWord($this->logRecord->status); ?>
							<?php endif; ?>
						</h3>
						
						<?php if (@$this->tables && is_array($this->tables) && count($this->tables)) : ?>
							<?php foreach ($this->tables as $title => $table) : ?>
								<?php
									$maxCells = 0;
									foreach ($table as $r) {
										if (count($r) > $maxCells)
											$maxCells = count($r);
									}
									
									$i = 0;
								?>
								<?php if (!is_int($title)) : ?>
								<h3><?php echo JText::_($title); ?></h3>
								<?php endif; ?>
								<table class="adminlist" width="100%">
									<tbody>
										<?php foreach ($table as $row) : ?>
										<tr class="row<?php echo $i; $i++; ?>">
											<?php 
											$offset = $maxCells - count($row);
											$k = 0; 
											?>
											<?php for ($j = 0, $c = count($row); $j < $c; $j++): ?>
												<?php if ($j == $c - 1): ?>
													<td colspan="<?php echo $offset; ?>">
												<?php else : ?>
													<td>
												<?php endif; ?>
												<?php echo $row[ $j ]; ?>
												</td>
											<?php endfor; ?>
											<?php if ($offset) : ?>
											<?php endif; ?>
										</tr>
										<?php endforeach; ?>
									</tbody>
								</table>
							<?php endforeach; ?>
						<?php endif; ?>
						<div id="file_contents"></div>
					</div>
					<div class="clr"></div>
					<?php endif; ?>
				</div>
				<div class="b">
					<div class="b">
						<div class="b"></div>
					</div>
				</div>
			</div>
			<noscript>
				Warning! JavaScript must be enabled for proper operation of the Administrator back-end.				
			</noscript>
			<div class="clr"></div>
		</div>
	</div>
</div>
<div id="border-bottom"><div><div/></div></div>
<div id="footer"></div>
</div>
<input type="hidden" name="id" id="log_id" value="<?php echo $this->logRecord->id; ?>" />
<input type="hidden" name="controller" value="log" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="type" value="<?php echo $this->logRecord->type; ?>" />
<?php echo JHTML::_('form.token'); ?>
</form>