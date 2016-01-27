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
defined ('_JEXEC') or die('Restricted Access');

JHTML::_('behavior.tooltip');

?>
<script>
	var scantimer = null;

	var stopScan = false;

	var requestSent = false;
	
	function onScanComplete() {
		stopScan = true;
		document.getElementById('scanButton').removeAttribute('disabled');

		toggleScanControls();

		window.onbeforeunload = null;

		var loader = document.getElementById('ajax_loader_img');
		loader.parentNode.removeChild(loader);

		clearInterval(scantimer);

		setTimeout(function() {
			alert('<?php echo JText::_('System scan complete', true); ?>');
		}, 2500);

		xajax_jdScanEnd();
	}

	function onInfoUpdated(obj) {
		if ( !obj || obj == '')
			return;

		obj = eval('(' + obj + ')');

		if (!obj)
			return;
		
		var infoArea = document.getElementById('scaninfo');
		var infoTable = document.getElementById('scantable');

		if (infoTable)
			infoTable.parentNode.removeChild(infoTable);
		
		var table = document.createElement('TABLE');
		var tbody = document.createElement('TBODY');
		table.id = 'scantable';
		table.style.width = '400px';
		table.className = 'adminlist';

		var r = 0;


		requestSent = false;
		
		if (obj['files']) {
			var tr = document.createElement('TR');
			var td1 = document.createElement('TD');
			var td2 = document.createElement('TD');

			td1.innerHTML = '<?php echo JText::_('Files Scanned', true); ?>: ';
			td2.innerHTML = '<b>' + obj['files'] + '</b>';

			tr.className = 'row' + r;
			tr.appendChild(td1);
			tr.appendChild(td2);
			tbody.appendChild(tr);
			r = 1 - r;
		}
		if (obj['dirs']) {
			var tr = document.createElement('TR');
			var td1 = document.createElement('TD');
			var td2 = document.createElement('TD');

			td1.innerHTML = '<?php echo JText::_('Directories Scanned', true); ?>: ';
			td2.innerHTML = '<b>' + obj['dirs'] + '</b>';

			tr.className = 'row' + r;
			tr.appendChild(td1);
			tr.appendChild(td2);
			tbody.appendChild(tr);
			r = 1 - r;
		}
		
		for (var i in obj) {
			if (i == 'dirs' || i == 'files' || i == 'total')
				continue;
			
			var tr = document.createElement('TR');
			var td1 = document.createElement('TD');
			var td2 = document.createElement('TD');

			td1.innerHTML = i + ': ';
			td2.innerHTML = '<b>' + obj[ i ] + '</b>';

			tr.className = 'row' + r;
			tr.appendChild(td1);
			tr.appendChild(td2);
			tbody.appendChild(tr);
			r = 1 - r;
		}
		
		table.appendChild(tbody);
		infoArea.appendChild(table);
	}	

	function startScan() {
		var infoArea = document.getElementById('scaninfo');
		infoArea.innerHTML = '';
		
		var scanStatus = document.getElementById('scanstatus');
		scanStatus.innerHTML = '<?php echo JText::_('Initializing', true); ?>';
		scanStatus.style.color = '';
		
		window.onbeforeunload = confirmAway;
		

		var loader = document.getElementById('ajax_loader_img');
		if (loader)
			loader.parentNode.removeChild(loader);
		
		loader = document.createElement('IMG');
		loader.src = '<?php echo JURI::base(); ?>components/com_jdefender/images/bar.gif';
		loader.id = 'ajax_loader_img';


		var fx = new Fx.Style('welcome_text', 'opacity', {duration: 800});
		fx.start(1, 0).chain(function() {
			var f = new Fx.Style('welcome_text', 'height', {duration: 500});
			f.start($('welcome_text').offsetHeight, 0).chain(function() {
				$('welcome_text').remove();
			});
		});

		var progressArea = document.getElementById('progressarea');

		progressArea.appendChild(loader);
		
		// --- 
		var scanButton = document.getElementById('scanButton');
		scanButton.setAttribute('disabled', 'disabled');

		xajax_jdStartScan(true);

		stopScan = false;
		requestSent = true;		
	
		scantimer = setInterval(function() {
			if (requestSent)
				return;
			else
				requestSent = true;
			

			if (stopScan) {
				clearInterval(scantimer);
				return;
			}
			
			xajax_jdGetScanStatus();
			
		}, 2500);
	

		return false;
	}

	function cancelScan() {
		if (confirm(confirmAway(true))) {
			// Cancel scan
			window.onbeforeunload = null;
			
			stopScan = true;
			xajax_jdScanEnd(true);

			document.getElementById('scanstatus').style.color = 'red';
			toggleScanControls();
		}
	}

	function onStartScanComplete(files, folders) {
		requestSent = false;

		document.getElementById('scanButton').removeAttribute('disabled');
		toggleScanControls();
	}

	function confirmAway(cancel) {
		if (cancel === true)
			return '<?php echo JText::_('Are you sure?'); ?>';
		else
			return '<?php echo JText::_('Scan is in progress.', true); ?>';
	}

	// ------------------------------------
	function blink(id, times) {
		times = parseInt(times);
		if (!times)
			return;
		
		var fx = new Fx.Style(id, 'opacity', {duration: 100});
		fx.start(1, 0).chain(function() {
			fx.start(0, 1).chain(function() {
				blink(id, times - 1);
			});
		});
	}

	function toggleScanControls() {
		var s = $('scanButton');
		var stop = $('stopButton');
		if (s.getStyle('display') != 'none') 
		{
			s.setAttribute('disabled', 'disabled');
			s.effect('opacity').start(1, 0).chain(function() {
				s.setStyle('display', 'none').removeAttribute('disabled');
				stop.setStyle('display', 'block').setStyle('opacity', 1);
			});
		}
		else {
			stop.setAttribute('disabled', 'disabled');
			stop.effect('opacity').start(1, 0).chain(function() {
				s.setStyle('display', 'block').setStyle('opacity', 1);
				stop.setStyle('display', 'none').removeAttribute('disabled');
			});
		}
	}

	function setProgress(percents) {
		var img = document.getElementById('ajax_loader_img');
		if (img) {
			var offset = -parseInt((100 - parseInt(percents)) * 250 / 100);
			img.style.backgroundPosition = offset + 'px';
		}
	}
</script>
<style>
#ajax_loader_img {
	width: 250px;
	height: 12px;
	background-position: -250px;
	background-image: URL(<?php echo JURI::base(); ?>components/com_jdefender/images/progress.gif);
	background-repeat: no-repeat;
}
#stopButton {
	color: red;
}

#scanstatus {
	width: 250px;
	margin: 0px;
	text-align: center;
}
#progressarea {
	margin-bottom: 10px;
}
</style>
<div>
<form name="adminForm" id="adminForm" action="" method="post">
	<div id="welcome_text">
		<h3>
			<?php echo JText::_('Last scanned')?>: <?php echo $this->lastScanDate; ?>
			
			<?php echo JText::_('Total Files'); ?>: 
			<?php if ($this->totalFiles) : ?>
				<?php echo $this->totalFiles; ?>
			<?php else: ?>
				<?php echo JText::_('Undefined'); ?>
			<?php endif; ?>
		</h3>
		
		<div style="font-size: 16px; font-family: Helvetica, Verdana;">
			<?php if (JDEBUG) : ?>
				<span style="color: red">
					<?php echo JText::_('Debug mode is on! Enabling debug mode will cause huge slowdown.'); ?><br />
					<?php echo JText::_('Please'), ' ', JHTML::link('index.php?option=com_config', JText::_('turn off')), ' ', JText::_('debug mode'); ?>
				</span>
			<?php else: ?>
				<?php if ($this->needScan) : ?>
					<?php echo JText::_('The filesystem was never scanned. You must perform the scan to enable filesystem monitoring. Scan may take several minutes.'); ?>
				<?php endif; ?>
			<?php endif; ?>
		</div>
		
		<h3 style="font-weight: normal; font-style: italic;">
			<?php echo JText::_('You can change scan settings'), ' ', JHTML::link('index.php?option=com_jdefender&controller=configuration#jd_config_scanner', JText::_('here')); ?>
		</h3>
	</div>

	<?php if (!JDEBUG && !$this->safeMode) : ?>
		<br />
		
	
	<div>
		<input id="scanButton" type="button" class="button" onclick="startScan();" value="<?php echo JText::_('Scan now!'); ?>" />
		<input id="stopButton" type="button" class="button" onclick="cancelScan();" value="<?php echo JText::_('Stop Scan!'); ?>" style="display:none" />
	</div>
	<div id="progressarea">
		<h3 id="scanstatus"></h3>
	</div>
	<?php endif; ?>
	<div id="scaninfo"></div>
	
	<?php echo JHTML::_('form.token'); ?>
	<input type="hidden" name="controller" value="scan" /> 
	<input type="hidden" name="option" value="com_jdefender" /> 
	<input type="hidden" name="task" value="" /> 
	<input type="hidden" name="firstScan" value="<?php echo (int)$this->needScan; ?>" />
</form>
</div>