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
jimport('joomla.application.component.controller');
class JDefenderControllerRestore extends JController
{
  function __construct()
  {

    parent::__construct();
    $this->registerTask('add', 'newRst');
    $this->registerTask('create', 'jdCreateBackup');
    $this->registerTask('restore', 'jdRestore');
  }
  function display()
  {
    JRequest::setVar('view', 'restore');
    parent::display();

  }
  function newRst()
  {
    JRequest::setVar('view', 'restore');
    JRequest::setVar('layout', 'form');
    JRequest::setVar('hidemainmenu', 1);
    parent::display();
  }
  function getTablesCreate($tables)
  {
    $database = &JFactory::getDBO();
    $result = array();

    foreach ($tables as $tblval)
    {
      $database->setQuery('SHOW CREATE table ' . $tblval);
      $database->query();
      $res = $database->loadResultArray(1);
      $result[$tblval] = $res[0];
    }

    return $result;
  }

  function jdCreateBackup()
  {
    $post = JRequest::get('post');
    jimport('joomla.filesystem.archive');
    jimport('joomla.filesystem.file');
    $database = &JFactory::getDBO();
    if (preg_match("/\\|\[|\]|\{|\}|\+|\-|\(|\)|\~|\`|\!|\@|\%|\^|\&|\*|\.|\,|\/|\?|\<|\>|\;|\:|\'|\"|\||_/", $post['param']['name']))
    {
      $this->setRedirect( 'index.php?option=com_jdefender&controller=restore', 'Only numerical and alphabetic characters can be in the name', 'notice');
      return;
    }
    if (!count($post['folder']) and $post['param']['home']!=1 and !count($post['table']))
    {
      $this->setRedirect( 'index.php?option=com_jdefender&controller=restore', 'There is nothing selected to create backup', 'notice');
      return;
    }
    set_time_limit(0);
    ignore_user_abort(true);
    $jconfig = new JConfig();
    $folders = array();
    $path = str_replace("\\", '/', JPATH_ROOT);
    foreach ($post[folder] as $element)
    {
      $folders[] = substr(str_replace($path, '', $element), 1);
    }
    //print_r($folders);
    if ($post['param']['name'] == '') $name = time() . '_noName_' . $post['param']['type'];
    else  $name = time() . '_' . $post['param']['name'] . '_' . $post['param']['type'];
    $home = array();
    $archive = new JArchive;



    if (count($post[table]) > 0)
    {
      set_magic_quotes_runtime(0);
      $tables_create = $this->jdTableCreate($post['table']);

      //print "<pre>";
      //print_r($tables_create);
      //print "</pre>";
      $fp = fopen('database_backup.sql', "w");
      $comments = "/* -------JDefender Backup-------- \n";
      $comments .= $post['param']['comments'];
      $comments .= " */\n";
      //print $comments;
      fwrite($fp, $comments);
      foreach ($tables_create as $table_name => $fld)
      {
        if ($post['param']['drop'] == '1') fwrite($fp, "DROP TABLE IF EXISTS `$table_name`;/*jdSePaRaTor*/\n");
      }

      foreach ($tables_create as $table_name => $fld)
      {

        $database->setQuery("SHOW TABLE STATUS FROM `" . $jconfig->db . "` like '" . $table_name . "'");
        $auto_inc = $database->loadObjectList();
        $auto_inc = number_format($auto_inc[0]->Auto_increment);
        //print_r($auto_inc);

        if ($post['param']['exist'] == '1') fwrite($fp, str_replace("CREATE TABLE",
            "CREATE TABLE IF NOT EXISTS", $fld));
        else  fwrite($fp, $fld);
        if ($post['param']['auto'] == '1') fwrite($fp, " AUTO_INCREMENT=$auto_inc");
        fwrite($fp, ";/*jdSePaRaTor*/\n");
        $sql = "SELECT * FROM " . str_replace($jconfig->dbprefix, "#__", $table_name);
        $database->setQuery($sql);
        $result = $database->loadAssocList();
        foreach ($result as $res)
        {
          //print "<pre>";
          //print_r($res);
          //print "</pre>";
          if (is_array($res))
          {
            $upper_lim = count($res);
            $counter = 1;
            $query = "INSERT INTO `$table_name` VALUES(";
            foreach ($res as $field_name => $field_value)
            {
              if ($counter == $upper_lim)
              {
                if (is_numeric($field_value))
                {
                  $query .= "$field_value";
                }
                else
                {

                  //$field_value = ereg_replace("\r\n|\n", mysql_escape_string("\r\n"), $field_value);
                  $field_value = mysql_escape_string($field_value);
                  $field_value = " '" . str_replace('\\\'', '\'\'', $field_value) . "'";
                  $query .= $field_value;
                }
              }
              else
              {
                if (is_numeric($field_value))
                {
                  $query .= "$field_value , ";
                }
                else
                {

                  //$field_value = ereg_replace("\r\n|\n", mysql_escape_string("\r\n"), $field_value);
                  $field_value = mysql_escape_string($field_value);
                  $field_value = " '" . str_replace('\\\'', '\'\'', $field_value) . "',";
                  $query .= $field_value;
                }
                $counter++;
              }
            }
            $query .= ");/*jdSePaRaTor*/\n";
            //print "</br>" . $query;
            fwrite($fp, $query);
          }
        }
      }
      fclose($fp);

    }
    chdir('../');
    $fileClass = new JFile;
    if ($post['param']['home'] == 1)
    {
      $home = array();
      $dir = opendir('.');
      while ($file = readdir($dir))
      {
        if ($file != '.' and $file != '..')
        {
          $home[] = $file;
        }
      }

      $archive->create("administrator/backups/$name", $home, $post['param']['arc_type'],'', '', true, false);
      if (file_exists('administrator/database_backup.sql'))
      {
        $fileClass->delete('administrator/database_backup.sql');
      }

    }
    else
      if (count($folders) > 0)
      {
        if (file_exists('administrator/database_backup.sql')
            and !(in_array('administrator',$folders))
            and !in_array('administrator/database_backup.sql', $folders))
        {
          $folders[] = 'administrator/database_backup.sql';
        }
        $archive->create("administrator/backups/$name", $folders, $post['param']['arc_type'],'', '', true, false);
        if (file_exists('administrator/database_backup.sql'))
        {
          $fileClass->delete('administrator/database_backup.sql');
        }
      }
      else
        if (!(count($folders) > 0) and $post['param']['home'] != 1 and count($post['table']) >
          0)
        {
          $archive->create("administrator/backups/$name",'administrator/database_backup.sql', $post['param']['arc_type'], '', '', true, false);
          $fileClass->delete('administrator/database_backup.sql');

        }
    chdir('administrator');
    $this->setRedirect( 'index.php?option=com_jdefender&controller=restore', JText::_('The Backup was Created Successfuly'));
  }


  function jdTableCreate($tables)
  {
    $database = &JFactory::getDBO();
    $result = array();

    foreach ($tables as $tblval)
    {
      $database->setQuery('SHOW CREATE table ' . $tblval);
      $database->query();
      $res = $database->loadResultArray(1);
      $result[$tblval] = $res[0];
    }

    return $result;
  }

  function jdRestore()
  {
    $post = JRequest::get('post');
    $to_restore = $post['cid'];
    if (count($to_restore) > 1)
    {
      $this->setRedirect( 'index.php?option=com_jdefender&controller=restore', JText::_('Only one backup can be selected to Restore'), 'notice');
      return;
    } else
    if (count($to_restore) == 0)
    {
      $this->setRedirect( 'index.php?option=com_jdefender&controller=restore', JText::_('You have to select the backup to restore first'), 'notice');
      return;
    }
    set_time_limit(0);
    ignore_user_abort(true);
    jimport('joomla.filesystem.archive');
    jimport('joomla.filesystem.file');
    $fp = new JFile;
    $database = &JFactory::getDBO();
    $archive = new JArchive('tar');
    $file = $to_restore[0];
    $archive->extract('backups/'.$file, '../');
     if ($fp->exists('database_backup.sql'))
      {
        set_magic_quotes_runtime(0);

        $content = $fp->read('database_backup.sql');

        //print "<pre>".$content."</pre>";

        $content = explode(";/*jdSePaRaTor*/", $content);
        $num_queries = count($content);
        $counter = 1;
        foreach ($content as $query)
        {
          //print $query . "<br>";
          $database->setQuery($query);
          if ($database->query())
            $counter++;
          else
          {
            //print "<div align='left' style='color:red;'><pre>" . $database->getErrorMsg() . "</pre></div>";
            $this->setRedirect( 'index.php?option=com_jdefender&controller=restore', $database->getErrorMsg(), 'notice');
            return;

          }
          if ($counter == $num_queries)
          {
            $fp->delete('database_backup.sql');
            //print "Finished Success";
            $this->setRedirect( 'index.php?option=com_jdefender&controller=restore', JText::_('The Backup was Applied Successfuly'));
            return;
          }
        }

      }
    $this->setRedirect( 'index.php?option=com_jdefender&controller=restore', JText::_('The Backup was Applied Successfuly'));
    return true;
  }

  function remove()
  {
    $post = JRequest::get('post');
    jimport('joomla.filesystem.file');
    $fp = new JFile;
    foreach ($post['cid'] as $file)
    {
      $fp->delete(JPATH_ADMINISTRATOR.DS."backups".DS.$file);

    }
   $this->setRedirect( 'index.php?option=com_jdefender&controller=restore', JText::_('The backup(s) deleted successfuly'));
  }
}
?>