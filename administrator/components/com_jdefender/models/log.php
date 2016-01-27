<?php
/**
 * $Id: log.php 7311 2011-08-19 11:18:02Z shitz $
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

class JDefenderModelLog extends JModel
{

	var $_data;
	var $_total;
	var $_pagination = null;

	function __construct()
	{
		parent::__construct();
		global $mainframe, $option;

		// Get the pagination request variables
//		$limit		= $mainframe->getUserStateFromRequest( $option.'.list.limit', 	'limit', 		$mainframe->getCfg('list_limit'), 	'int' );
//		$limitstart	= $mainframe->getUserStateFromRequest( $option.'limitstart', 	'limitstart', 	0, 									'int' );

//		$this->setState('limit', $limit);
//		$this->setState('limitstart', $limitstart);

	}

	function getPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_pagination))
		{
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit'));
		}

		return $this->_pagination;
	}

	function getData()
	{
		$query = $this->_buildQuery();
		return $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
	}
	
	function getIds() {
		$data = $this->getData();
		
		$ids = array();
		foreach ($data as $d)
			$ids [] = $d->id;
			
		return $ids;
	}
	
	function getStatusesForType($type = null)
	{	
		$where = array();
		if ($type)
			$where [] = '`type` = '.$this->_db->Quote($type);
			
		$extension = $this->getState('filter_extension');
		if ($extension)
			$where [] = '`extension` = '.$this->_db->Quote($extension);
	
		$where = count($where) ? 'WHERE '.implode(' AND ', $where) : ' '; 

		$q = 'SELECT `status` FROM #__jdefender_log '
			.$where
			.' GROUP BY `status`';

		return $this->_getList( $q );
	}


	function getLogGroups() {
		$q = 'SELECT '.
				'type, COUNT(*) AS total, MAX(ctime) AS ctime '.
			' FROM '. 
				' #__jdefender_log '.
			'GROUP by type '.
			'ORDER by type';
		
		return $this->_getList( $q );
	}
	
	function getTotalLogGroups() {
		$q = 'SELECT COUNT(DISTINCT `type`) FROM #__jdefender_log';
		$this->_db->setQuery( $q );
		return $this->_db->loadResult();
	}
	

	function getTotal()
	{
		$query = $this->_buildQuery();
		return $this->_getListCount($query);
	}
	
	function getNotCount($type, $status) {
		$db = & JFactory::getDBO();
		
		$q = 'SELECT COUNT( * ) FROM #__jdefender_log WHERE type = '.$db->Quote($type).' AND `status` <> '.$db->Quote($status);
		$db->setQuery( $q );
		
		return $db->loadResult();
	}

	function getExtensionMetadataForLogs($ids) {
		if (empty($ids))
			return null;
		
		require_once JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_jdefender'.DS.'lib'.DS.'utils'.DS.'jd_extension_resolver.php';
		
		settype($ids, 'array');
		JArrayHelper::toInteger($ids);
		$resolver = new JD_Extension_Resolver();
		
		$state = $this->getState();
		$this->_state = new JObject();
		$this->setState('id', $ids);
		
		$records = $this->getData();
		
		$this->_state = $state;

		
		$results = array();
		
		foreach ($records as $record) {
			$metadata = $resolver->getExtensionMetadata($record->url);
			
			if (empty($metadata))
				continue;
			
			if (!empty($metadata->author) && !empty($metadata->authorEmail)) {
				$type 		= $metadata->jd_extension_info->type;
				$extension 	= $metadata->jd_extension_info->extension;
				
				if (empty($results[$type][$extension]['info'])) {
					$results[$type][$extension]['info'] = array($metadata->author, $metadata->authorEmail);
					$results[$type][$extension]['meta'] = $metadata;
				}
				
				if (empty($results[$type][$extension]['records']))
					$results[$type][$extension]['records'] = array();
					
				$results[$type][$extension]['records'][] = $record;
			}
		}
		
		return $results;
	}
	
	function getLoggedExtensions() {
		$type = $this->getState('type');
		
		$q = 'SELECT * FROM #__jdefender_log ';
		if ($type)
			$q .= ' WHERE `type` = '.$this->_db->Quote($type);
			
		$q .= ' GROUP BY `extension`';
		return $this->_getList( $q );
	}

	function _buildQuery()
	{
		// Get the WHERE and ORDER BY clauses for the query
		$where 		= $this->_where();
		$orderby 	= $this->_orderBy();
		
		
		$query = "SELECT l.*, b.value AS blocked_ip "
			.'FROM 		#__jdefender_log 		AS l '
			.'LEFT JOIN #__jdefender_block_list AS b ON b.type = "ip" AND b.value = l.ip AND b.published = 1'
			.$where. '  '.$orderby;

		return $query;
	}
	
	function _where() 
	{
		$where = array();
		
		$type	= $this->getState('type');
		$search = $this->getState('filter_search');
		$state 	= $this->getState('filter_state');
		$ext	= $this->getState('filter_extension');
		$ids 	= $this->getState('id');
//		
//		echo '<pre>';
//		var_dump($this->_state);
//		die;
		
		if (!empty($ids)) {
			settype($ids, 'array');
			JArrayHelper::toInteger($ids);
			
			$where [] = ' l.`id` IN ('.implode(', ', $ids).') ';
		}
			
		
		if (!empty($type))
			$where [] = 'l.`type` = '.$this->_db->Quote($type);
		
		if (!empty($state)) {
			if (is_array($state)) {
				foreach ($state as $k => $v)
					$state[ $k ] = $this->_db->Quote( $v );
				$where [] = 'l.`status` IN ('.implode(', ', $state).')';
			}
			else
				$where [] = 'l.`status` = '.$this->_db->Quote($state);
		}
		
		if (!empty($search)) {
			$or = array();
			$or [] = 'l.`status` LIKE '.$this->_db->Quote('%'.JString::strtolower($search).'%');
			$or [] = 'l.`url` 	 LIKE '.$this->_db->Quote('%'.JString::strtolower($search).'%');
			$or [] = 'l.`ip` 	 LIKE '.$this->_db->Quote('%'.JString::strtolower($search).'%');
			$or [] = 'l.`post` 	 LIKE '.$this->_db->Quote('%'.JString::strtolower($search).'%');
			
			$where [] = '('. implode(' OR ', $or) .')';
		}
		
		if (!empty($ext)) {
			$where [] = 'l.`extension` = '.$this->_db->Quote($ext);
		}
		
		return count($where) ? ' WHERE '.implode(' AND ', $where) : '';
	}

	function _orderBy()
	{
		$filter_order = $this->getState('filter_order');
		if (!$filter_order)
			$filter_order = 'ctime';
		
		$filter_order_Dir = $this->getState('filter_order_Dir');

		$orderBy = "ORDER BY ".$filter_order." ".$filter_order_Dir;
		
		return $orderBy;
	}
	
	function deleteGroups($groups) {
		settype($groups, 'array');
		
		if (count($groups)) {
			foreach ($groups as $k => $v) {
				$groups[ $k ] = $this->_db->Quote( $v );
			}
			
			$q = 'DELETE FROM #__jdefender_log WHERE `type` IN ('.implode(', ', $groups).')';
			$this->_db->setQuery( $q );
			$this->_db->query();
			
			return $this->_db->getAffectedRows();
		}
		return 0;
	}

	function delete($ids)
	{
		if (count($ids)) {
			$q = 'DELETE FROM #__jdefender_log WHERE id IN ('.implode(', ', $ids).')';
			$this->_db->setQuery( $q );
			$this->_db->query();
		}
		return true;
	}

	function deleteBlockedIpLogs($logTypes = false) {
		$where = array();
		$where [] =  'block.`type` = "ip"';
		
		if ($logTypes) {
			settype($logTypes, 'array');
			foreach ($logTypes as $k => $v)
				$logTypes [ $k ] = $this->_db->Quote( $v );
				
			$where [] = 'logs.`type` IN ('.implode(', ', $logTypes).') AND block.`published` = 1 ';
		}
		
		$where = ' WHERE '.implode(' AND ', $where);
		
		$q = 'DELETE logs.* FROM #__jdefender_log logs '.
			'INNER JOIN #__jdefender_block_list block ON logs.ip = block.value '.
			$where;
			
		$this->_db->setQuery( $q );
		
		if (!$this->_db->query())
			return false;
			
		$deletedCount =  $this->_db->getAffectedRows();

		
		
		// Deletes logs generated for blocked IP Ranges
		
		$ranges = & JD_Block_Helper::getRangedIpBlocks();
		
		$ipWhere = array();
		if ($ranges && is_array($ranges)) {
			foreach ($ranges as $r) {
				$t = explode('-', $r);
				
				if (count($t) != 2)
					continue;
				
				$t[0] = JString::trim($t[0]);
				$t[1] = JString::trim($t[1]);
					
				$lower = ip2long($t[0]);
				$upper = ip2long($t[1]);
				
				if ($lower == -1 || $lower === false || $upper == -1 || $upper == false) {
					continue;
				}
				
				if ($lower > $upper) {
					$temp = $t[0];
					$t[0] = $t[1];
					$t[1] = $temp;
				}
				
				$ipWhere [] = 'INET_ATON(`ip`) BETWEEN INET_ATON('.$this->_db->Quote($t[0]).') AND INET_ATON('.$this->_db->Quote($t[1]).')';
			}
		}
		
		$where = array();
		if ($logTypes) {
			$where [] = '`type` IN ('.implode(', ', $logTypes).') ';
		}
		
		if (count($ipWhere))
		{
			$where [] = ' ( '.implode (' OR ', $ipWhere).' ) ';
		
			if (count($where))
				$where = 'WHERE '.implode(' AND ', $where);
			else
				$where = '';
		
			$q = 'DELETE FROM #__jdefender_log '. $where;
			$this->_db->setQuery( $q );
			
			if ( ! $this->_db->query())
				return false;
			
			$deletedCount += $this->_db->getAffectedRows();
		}
		
		return $deletedCount;
	}
	
	function setLogStatus($logIds, $status) {
		settype($logIds, 'array');
		JArrayHelper::toInteger($logIds);
		$q = 'UPDATE #__jdefender_log SET `status` = '.$this->_db->Quote($status).' WHERE id IN ('.implode(', ', $logIds).') ';
		
		$this->_db->setQuery( $q );
		
		if (!$this->_db->query())
			return false;
			
		return $this->_db->getAffectedRows();
	}
}
