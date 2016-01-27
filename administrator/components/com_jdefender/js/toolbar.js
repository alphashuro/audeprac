/**
 * @author nurlan
 * Helper object, used with ME_Defender_Toolbar.
 * The method names are mapped directly to the ME_Defender_Toolbar methods :)
 * The code is written to work within the SqueezeBox.  
 */
var ME_Defender = {};

ME_Defender.Toolbar =  {
	submit: function(task) {
		var me 		= ME_Defender.Toolbar;
		var logId 	= me._getLogId();
		
		if (logId === false)
			return;
		
		
		document.adminForm.task.value = task + 'Log';
		document.adminForm.submit();
	},
	
	closeSqueezeNRedirect: function() {
		setTimeout(function() {
			parent.SqueezeBox.close();
			parent.window.location = parent.window.location; 
		}, 500);
	},
	
	/**
	 * Returns the log id, by looking up the page.
	 */
	_getLogId: function() {
		var input = document.getElementById('log_id');
		if (!input) {
			alert('Error, cannot find control variables found, please reload the page');
			return false;
		}
		
		return input.value;
	}
}



