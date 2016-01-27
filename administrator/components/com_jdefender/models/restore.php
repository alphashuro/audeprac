<?php
/**
 * $Id: restore.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined("_JEXEC") or die("Restricted Access");
  jimport('joomla.application.component.model');

  class JDefenderModelRestore extends JModel
  {
    var $_data;
    var $_total;
    var $_pagination = null;

    function __construct()
    {
      parent::__construct();
    }

    function getData()
    {
      $path = JPATH_ADMINISTRATOR . DS . 'backups';
      $ret = array();
      if ($dir = @opendir($path))
      {
        $this->_total = 0;
        while ($file = readdir($dir))
        {
          if (preg_match("/zip$|tar$|bzip2$|gz$/", $file) and $file != '.' and $file != '..')
          {

            $array = explode("_", $file);
            $fullname = $file;
            $time  = $array[0];
            $name  = $array[1];
            $type  = preg_replace("/\.zip$|\.tar$|\.bzip2$|\.gz$/", '', $array[2]);
            $size  = $this->jdGetSizes(filesize(JPATH_ADMINISTRATOR.DS.'backups'.DS.$file));
            $ret[] = array('name' => $name, 'time' => $time, 'type' => $type, 'size' => $size, 'fullname' => $fullname);
            $this->_total++;
          }

        }
      }
       return $ret;
    }

    function jdGetSizes($size)
    {
    if ($size < 1024)
      $size = number_format(Round($size, 3), 0, ',', '.') . " B";
    elseif ($size < 1048576)
      $size = number_format(Round($size / 1024, 3), 2, ',', '.') . " KB";
    elseif ($size < 1073741824)
      $size = number_format(Round($size / 1048576, 3), 2, ',', '.') . " MB";
    elseif (1073741824 < $size)
      $size = number_format(Round($size / 1073741824, 3), 2, ',', '.') . " GB";
    elseif (1099511627776 < $size)
      $size = number_format(Round($size / 1099511627776, 3), 2, ',', '.') . " TB";
    return $size;
    }

    function getPagination()
    {
      // Lets load the content if it doesn't already exist
      if (empty($this->_pagination))
      {
        jimport('joomla.html.pagination');
        $this->_pagination = new JPagination($this->_total, $this->getState('limitstart'), $this->getState('limit'));
      }

      return $this->_pagination;
    }



    function getTablesInfo()
    {
        $jconf = new JConfig;
        $database = &JFactory::getDBO();
        $sql = "SHOW TABLE STATUS FROM `".$jconf->db."`";
        $database->setQuery($sql);
        $tables = $database->loadObjectList();
        $ret = array();
        foreach( $tables as $table){
          $name = $table->Name;
          $rows = number_format($table->Rows, 0, ',', '.');
          $size = $this->jdGetSizes($table->Data_length);
          $overhead = $this->jdGetSizes($table->Data_free);
          $auto_increment = number_format($table->Auto_increment, 0, ',', '.');

          $ret[] = array('name' => $name, 'rows' => $rows, 'size' => $size,
                         'overhead' => $overhead, 'auto_inc' => $auto_increment);
        }
    return $ret;
    }

  }
?>