<?php
/**
 * $Id: xajax.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined('_JEXEC') or die('Restricted Access');

require_once (JPATH_COMPONENT_ADMINISTRATOR . DS . 'xajax' . DS . 'xajax_core' . DS . 'xajax.inc.php');

$xajax = new xajax('index3.php?option=com_jdefender&controller='.JRequest::getVar('controller'));

$xajax->registerFunction("jdBlockParam");
$xajax->registerFunction("jdStartScan");
$xajax->registerFunction("jdScanEnd");
$xajax->registerFunction("jdGetScanStatus");

$xajax->registerFunction("jdPublishBlock");
$xajax->registerFunction("jdPublishRule");
$xajax->registerFunction("jdReadFile");

//$xajax->registerFunction("open_folder");
//$xajax->registerFunction("close_folder");
//$xajax->registerFunction("jdGetDirectory");

$xajax->processRequest();
$xajax->printJavascript('components/com_jdefender/xajax/');


function jdGetScanStatus() {
	require_once JPATH_COMPONENT_ADMINISTRATOR .DS .'helpers' .DS .'log.php';
	
	$objResponse = new xajaxResponse();
	
	$controller = new JDefenderControllerScan();
	$scanStatus = $controller->scan($doLog = JD_Scan_Helper::isLogging());
	
	
	$scanInfo = JD_Vars_Helper::getGroup('jdefender_scan');
	if (empty($scanInfo))
		$scanInfo = array();
	
	unset($scanInfo['status']);
	
	$progress = 0;
	if (!empty($scanInfo['total'])) {
		$scanned = 0;
		$scanned += @$scanInfo['files'];
		$scanned += @$scanInfo['dirs'];
		
		$progress = (int)floor(($scanned / $scanInfo['total']) * 100);
	}
	
	$objResponse->assign('scanstatus', 'innerHTML', JText::_('Progress').': '.$progress.'%');
	$objResponse->script('setProgress('.$progress.');');
	$objResponse->script('blink("scanstatus", 2);');
		
	$js = array();
	foreach ($scanInfo as $k => $v) 
	{
		if ($k == 'last_scan_date')
			continue;
		
		$titles = JD_Log_Helper::readableLogType( $k );
		
		if ($titles)
			$js [] = '"'.addslashes(JHTML::link('index.php?option=com_jdefender&controller=log&task=showLog&cid[]='.$k, $titles->title, 'target="_blank"')).'" : '.(int)$v;
		else
			$js [] = $k.': '.(int)$v;
	}
	$js = '{'.implode(', ', $js).'}';
	
	$objResponse->script('onInfoUpdated("'.addslashes($js).'")');
	
	
	// Check scan status.
	if ($scanStatus && is_array($scanStatus) && count($scanStatus) == 2) {
		list($filesystemScanComplete, $optionScanComplete) = $scanStatus;
		
		if ($filesystemScanComplete && $optionScanComplete) {
			$objResponse->script('onScanComplete()');
		}
	}
		
	return $objResponse;
}

function jdStartScan($log) {
	require_once JPATH_COMPONENT_ADMINISTRATOR .DS .'controllers' .DS .'scan.php';
	
	$objResponse = new xajaxResponse();
	
	JD_Scan_Helper::cleanUpState();
	JD_Scan_Helper::setLogging($log);
	
	$controller = new JDefenderControllerScan();
	// Create file list
	$info = $controller->createScanFileList();
	
	$objResponse->script('onStartScanComplete('.(int)$info[ 0 ].', '.(int)$info[ 1 ].');');
	
	return $objResponse;
}

function jdScanEnd($cancel = false) {
	$objResponse = new xajaxResponse();
	
	JD_Scan_Helper::cleanUpState();
	
	if ($cancel) {
		$message = JText::_('System scan was cancelled'); 
	}
	else {
		$message = JText::_('System scan is complete');
		JD_Scan_Helper::setLastScanDate();
	}
  	
  	$objResponse->assign('scanstatus', 'innerHTML', $message);
  	
  	return $objResponse;
}


function jdReadFile($id) {
	jimport ('geshi.geshi');
	
	$id = (int)$id;
	$objResponse = new xajaxResponse();
	
	$row = & JTable::getInstance('Log', 'Table');
	if ( !$row->load($id) ) {
		$objResponse->alert(JText::_('Cannot find log entry'));
		return $objResponse;
	}
	
	if ( ! is_file($row->url)) {
		$objResponse->alert(JText::_('Cannot find file'));
		return $objResponse;
	}
	
	$lang = false;
	$ext = strtolower(JFile::getExt($row->url));
	
	switch ($ext) {
		case 'php':
		case 'css':
		case 'ini':
		case 'sql':
		case 'xml':
			$lang = $ext;
		case 'js':
			$lang = 'javascript';
	}
	
	$contents = JFile::read($row->url);
	
	$contents = geshi_highlight($contents, $lang, null, true);
	
	$objResponse->assign('file_contents', 'innerHTML', $contents);
	
	return $objResponse;
}

function jdBlockParam($id_img, $type, $value)
{
	$database =  &JFactory::getDBO();
	$objResponse = new xajaxResponse();
	
	if ($value)
	{
		$row = & JTable::getInstance('Block', 'Table');
		
		$exists = $row->loadByTypeAndValue($type, $value);

		if ( ($exists && !$row->published) || !$exists )
		{
			if ($exists) {
				$row->published = 1;
				$row->store();
			}
			else {
				$row->type 	= $type;
				$row->value = $value;
				$row->reason = JText::_('Not specified');
				$row->published = 1;
				$row->store();
			}
			
			$objResponse->assign($id_img, "src", "components/com_jdefender/images/locked.gif");
			$objResponse->assign($value, "src", "components/com_jdefender/images/locked.gif");
		}
		else
		{
			if ($exists) {
				$row->published = 0;
				$row->store();
			}
			else {
				$database->setQuery("DELETE FROM `#__jdefender_block_list` WHERE `type`='" . $type .
	          "' and `value`= '" . $value . "'");
				$database->query();
			}
			
			$objResponse->assign($value, "src", "components/com_jdefender/images/unlocked.gif");
			$objResponse->assign($id_img, "src", "components/com_jdefender/images/unlocked.gif");

		}
	}
	return $objResponse;
}

function jdPublishBlock($id) {
	$db = & JFactory::getDBO();
	
	$objResponse = new xajaxResponse();
	
	$db->setQuery('UPDATE #__jdefender_block_list SET `published` = 1 - `published` WHERE id = '.(int)$id);
	$db->query();
	
	return $objResponse;
}

function jdPublishRule($id) {
	$db = & JFactory::getDBO();
	
	$objResponse = new xajaxResponse();
	
	$db->setQuery('UPDATE #__jdefender_rules SET `published` = 1 - `published` WHERE id = '.(int)$id);
	$db->query();
	
	return $objResponse;
}

function jdGetDirectoryList($folder)
{
	$folder = str_replace('\\','/', $folder);
	if ($dir = @opendir($folder))
	{
		$list = '';
		while ($file = readdir($dir))
		{
			if (filetype($folder . '/' . $file) == 'dir' and $file != '.' and $file != '..')
			{
				$id = md5($folder . '/' . $file);
				$name = str_replace(':', '', $folder);
				$name = str_replace('/', '_', $name);
				$name .= "_" . $file;
				$home = $file;

				if (!is_readable($folder . '/' . $file))
				{
					$list .= "\n<div id=\"$id\" align='left'><span id='$name'>" .
              "<a  href='#'  onClick=\"xajax_open_folder('{$folder}/{$file}','$home','$name'); \">" .
              "<img src = 'components/com_jdefender/images/icons/folder.png' border='0'> </a></span>" .
              "<input type='checkbox' name='folder[$id]' id='folder[$id]' value='$folder/$file'><font color='red'>  $file</font><br></div>";
				}
				else
				{
					$list .= "\n<div id=\"$id\" align='left'><span id='$name'>" . "<a  href='#'  onClick=\"xajax_open_folder('{$folder}/{$file}','$home','$name'); \">" .
              "<img src = 'components/com_jdefender/images/icons/folder.png' border='0'> </a></span>" .
              "<input type='checkbox' name='folder[$id]' id='folder[$id]' value='$folder/$file'>  $file<br></div>";
				}

			}

		}
		rewind($dir);
		while ($file = readdir($dir))
		{
			if (filetype($folder . '/' . $file) != 'dir' and $file != '.' and $file != '..')
			{

				$ext = strtolower(strrchr($file,'.'));
				$ext = str_replace('.','',$ext);
				$id = md5($folder . '/' . $file);
				$name = str_replace(':', '', $folder);
				$name = str_replace('/', '_', $name);
				$name .= "_" . $file;
				$home = $file;

				if (!is_readable($folder . '/' . $file))
				{
					$list .= "\n<div id=\"$id\" align='left' style='margin-left:19px;'><input type='checkbox' name='folder[$id]' id='folder[$id]' value='$folder/$file'><font color='red'>  $file</font><br></div>";
				}
				else
				{
					$list .= "\n<div id=\"$id\" align='left'style='margin-left:19px;'><input type='checkbox' name='folder[$id]' id='folder[$id]' value='$folder/$file'>$file<br></div>";
				}

			}

		}
		closedir($dir);
	}
	$out = "<div style=\"padding-left:20px\">$list</div>";
	return $out;
}


function open_folder($folder, $home, $name = '')
{
	$objResponse = new xajaxResponse();
	//$objResponse->alert($folder);
	if (!is_readable($folder))
	{
		$objResponse->alert("The folder is not readable!");
		return $objResponse;

	}
	$out = jdGetDirectoryList($folder);
	$id = md5($folder);

	if ($name != '')
	{
		$objResponse->assign($name, "innerHTML", "<a href='#' onClick =\"xajax_close_folder('$folder','$home', '$name', '$id')\" ><img src = 'components/com_jdefender/images/icons/folder.png' border='0'> </a>");
	}

	$objResponse->append($id, "innerHTML", $out);
	return $objResponse;

}

function close_folder($folder, $home, $name, $id)
{
	$objResponse = new xajaxResponse();
	//$objResponse->alert($home);
	$name = dirname($folder);
	$out = '';
	if (!is_readable($folder))
	{
		$out = "\n<div id=\"$id\" align='left'><span id='$name'><a  href='#'  onClick=\"xajax_open_folder('$folder','$home','$name');  \">" .
        "<img src = 'components/com_jdefender/images/icons/folder.png' border='0'> </a></span>" .
        "<input type='checkbox' name='folder[$id]' id='folder[$id]' value='$folder'><font color='red'>  $home</font></div>";
	}
	else
	{
		$out = "\n<div id=\"$id\" align='left'><span id='$name'><a  href='#'  onClick=\"xajax_open_folder('$folder','$home','$name');  \">" .
        "<img src = 'components/com_jdefender/images/icons/folder.png' border='0'> </a></span>" .
        "<input type='checkbox' name='folder[$id]' id='folder[$id]' value='$folder'> $home</div>";
	}



	$objResponse->assign($id, 'innerHTML', $out);
	return $objResponse;
}

function jdGetDirectory($callback, $folder, $getFiles = false, $filter = '*') {
	jimport ('joomla.filesystem.file');
	jimport ('joomla.filesystem.file');
	
	$folder = JPath::clean(JPATH_ROOT.DS.$folder);
	JPath::check($folder);
	
	$response 	= new XajaxResponse();
	
	if (!is_dir($folder)) {
		return $response;
	}
	
	$result 	= array();
	
	$dirs = JFolder::folders($folder, '', false, true);
	foreach ($dirs as $dir) {
		$dirData = array();
		$dirData['permission'] = substr(sprintf("%o", fileperms($dir)), -3);
		
		if ($getFiles) {
			$fileData = array(); 
			$files = glob($folder.DS.$filter);

			foreach ($files as $f) {
				$fileData[ $f ]['permission'] = substr(sprintf("%o", fileperms($f)), -3);
			}
		}
		
		$result[ $dir ]['info'] 	= $dirData;
		$result[ $dir ]['files']	= $fileData;
	}
	
	$response->script($callback.'('.JHTMLBehavior::_getJSObject($result).')');
	
	return $response;
}

