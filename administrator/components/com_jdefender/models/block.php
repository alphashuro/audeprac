<?php
/**
 * $Id: block.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined("_JEXEC") or die("Restricted Access");
jimport ( 'joomla.application.component.model' );

class JDefenderModelBlock extends JModel {
	var $_pagination = null;
	
	function __construct() {
		parent::__construct ();
		global $mainframe, $option;
		
		// Get the pagination request variables
		$limit = $mainframe->getUserStateFromRequest ( 'global.list.limit', 'limit', $mainframe->getCfg ( 'list_limit' ), 'int' );
		$limitstart = $mainframe->getUserStateFromRequest ( $option . 'limitstart', 'limitstart', 0, 'int' );
		
		$this->setState ( 'limit', $limit );
		$this->setState ( 'limitstart', $limitstart );
	}
	
	function getPagination() {
		// Lets load the content if it doesn't already exist
		if (empty ( $this->_pagination )) {
			jimport ( 'joomla.html.pagination' );
			$this->_pagination = new JPagination ( $this->getTotal (), $this->getState ( 'limitstart' ), $this->getState ( 'limit' ) );
		}
		
		return $this->_pagination;
	}
	
	function getData() {
		$query = $this->_buildQuery ();
		return $this->_getList ( $query, $this->getState ( 'limitstart' ), $this->getState ( 'limit' ) );
	}
	
	function getTypes() {
		return $this->_getList ( 'SELECT `type` FROM #__jdefender_block_list GROUP BY `type`' );
	}
	
	function getTotal() {
		$query = $this->_buildQuery ();
		return $this->_getListCount ( $query );
	}
	
	function _buildQuery() {
		// Get the WHERE and ORDER BY clauses for the query
		$where = $this->_buildWhere();
		$orderby = $this->_orderBy ();
		
		$query = "SELECT * FROM #__jdefender_block_list " 
			.$where
			.$orderby;
		
		return $query;
	}
	
	function _buildWhere() {
		$type 	= $this->getState ( 'filter_type' );
		$id 	= $this->getState ( 'id' );
		$published = $this->getState ( 'published' );
		
		$where = array ();
		
		if ($type)
			$where [] = 'type = ' . $this->_db->Quote ( $type );
		if ($id) {
			settype($id, 'array');
			JArrayHelper::toInteger($id);
			
			$where [] = ' `id` IN ('.implode(', ', $id).') ';
		}
		if (is_numeric($published)) {
			$published = (int)$published;
			if ($published > 0) 
				$published = 1;
			else
				$published = 0;
				
			$where [] = ' `published` = '.$published;
		}
			
		if (count($where))
			$where = ' WHERE '.implode(' AND ', $where);
		else
			$where = '';
		
		return $where;
	}
	
	function _orderBy() {
		global $mainframe, $option;
		$filter_order = $mainframe->getUserStateFromRequest ( $option . '.block.filter_order', 'filter_order', 'type' );
		$filter_order_Dir = $mainframe->getUserStateFromRequest ( $option . '.block.filter_order_Dir', 'filter_order_Dir', '' );
		
		if ($filter_order) {
			$orderby = ' ORDER BY ' . $filter_order . ' ' . $filter_order_Dir . ' ';
		}
		return $orderby;
	}
	
	function delete($ids) {
		$table = & $this->getTable ();
		foreach ( $ids as $id ) {
			$table->delete ( $id );
		}
		return true;
	}
	
	function save($vars) {
		if (empty ( $vars )) {
			$this->setError ( 'No data to save' );
			return false;
		}
		
		$table = &$this->getTable ( 'Block' );
		
		foreach ( $vars as $k => $row ) {
			$table->id = null;
			$table->bind ( $row );
			
			if (! $table->check ()) {
				$this->setError ( $table->getError () );
				return false;
			}
		}
		
		foreach ( $vars as $row ) {
			$table->id = null;
			$table->bind ( $row );
			
			if (! $table->store ()) {
				$this->setError ( $table->getError () );
				return false;
			}
		}
		
		return true;
	}
	
	function publish($ids, $publish = true) {
		if (empty($ids))	
			return;
		
		settype($ids, 'array');
		JArrayHelper::toInteger($ids);
		
		$publish = $publish ? 1 : 0;
		
		$q = 'UPDATE #__jdefender_block_list SET published = '.$publish.' WHERE `id` IN ('.implode(', ', $ids).') ';
		$this->_db->setQuery($q);
		$this->_db->query(); 
	}
}
