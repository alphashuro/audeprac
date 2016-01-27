<?php
/**
 * $Id: rule.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined ('_JEXEC') or die('Restricted Access');

jimport ('joomla.application.component.model');

class JDefenderModelRule extends JModel
{	
	function & getData() {
		static $data = array();
		
		$q 		= $this->_buildQuery();
		$sig 	= md5($q.'_'.$this->getState('limitstart').'_'.$this->getState('limit'));
		
		if (empty($data[$sig]))
			$data[$sig] = $this->_getList($q, $this->getState('limitstart'), $this->getState('limit'));
			
		return $data[$sig];
	}
	
	/**
	 * Returns rules wrapped to TableRule.
	 */
	function getDataAsTables() {
		$data = & $this->getData();
		
		
		if (!$data)
			return array();
		
		$results = array();
		for ($i = 0, $c = count($data); $i < $c; $i++) {
			$table = & JTable::getInstance('Rule', 'Table');
			if ($table->bind($data[ $i ]))
				$results [] = $table;
		}
		
		return $results;
	}
	
	/**
	 * Fetches rule types
	 */
	function getTypes() {
		static $result = false;
		if ( $result === false )
		{
			$q = 'SELECT `type` FROM #__jdefender_rules GROUP BY `type`';
			$this->_db->setQuery( $q );
			
			$result = $this->_db->loadObjectList();
		}
		
		return $result;
	}
	
	
	function getTotal() {
		return $this->_getListCount($this->_buildQuery());
	}
	
	function getPagination() {
		jimport ('joomla.html.pagination');
		
		return new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit'));
	}
	
	function getComponents() {
		$q = 'SELECT `component` FROM #__jdefender_rules GROUP BY `component`';
		$this->_db->setQuery( $q );
		
		return $this->_db->loadResultArray( 0 );
	}
	
	function getOrigins() {
		$q = 'SELECT `origin` FROM #__jdefender_rules GROUP BY `origin`';
		$this->_db->setQuery( $q );
		
		return $this->_db->loadResultArray( 0 );
	}
	
	function getVariables() {
		$component = $this->getState('component');
		
		$q = 'SELECT `variable` FROM #__jdefender_rules ';
		
		if ($component)
			$q .= ' WHERE `component` = '.$this->_db->Quote($component);
			
		$q .= 'GROUP BY `variable`';
		
		$this->_db->setQuery( $q );
		
		return $this->_db->loadResultArray( 0 );
	}
	
	function _buildQuery() {
		$where = $this->_buildWhere();
		$order = $this->_buildOrder();
		
		$q = 'SELECT * FROM #__jdefender_rules r '.$where.' '. $order;
		
		return $q;
	}
	
	// @entodo: complete
	function _buildWhere() 
	{
		$where = array();
		
		$type		= $this->getState('type');
		$search	 	= $this->getState('search');
		$family 	= $this->getState('family');
		$ids 		= $this->getState('id');
		$component 	= $this->getState('component');
		$var 		= $this->getState('variable');
		$published	= $this->getState('published');
		
		if (!empty($ids)) {
			settype($ids, 'array');
			JArrayHelper::toInteger($ids);
			
			$where [] = ' r.`id` IN ('.implode(', ', $ids).') ';
		}
		
		if ($component)
			$where [] = ' (r.`component` = '.$this->_db->Quote($component).' OR r.`component` = "*") ';
		
		if (!empty($type))
			$where [] = 'r.`type` = '.$this->_db->Quote($type);
		
		if (!empty($family)) {
			if (is_array($family)) {
				foreach ($family as $k => $v)
					$family[ $k ] = $this->_db->Quote( $v );
				$where [] = 'r.`family` IN ('.implode(', ', $family).')';
			}
			else
				$where [] = 'r.`family` = '.$this->_db->Quote($family);
		}
		
		if (!empty($search)) {
			$or = array();
			$or [] = 'r.`family` 	LIKE '.$this->_db->Quote('%'.JString::strtolower($search).'%');
			$or [] = 'r.`type` 	 	LIKE '.$this->_db->Quote('%'.JString::strtolower($search).'%');
			$or [] = 'r.`rule` 	 	LIKE '.$this->_db->Quote('%'.JString::strtolower($search).'%');
			$or [] = 'r.`component`	LIKE '.$this->_db->Quote('%'.JString::strtolower($search).'%');
			$or [] = 'r.`variable` 	LIKE '.$this->_db->Quote('%'.JString::strtolower($search).'%');
			
			$where [] = '('. implode(' OR ', $or) .')';
		}
		
		if (!is_null($published))
			$where [] = 'r.`published` = '.(int)$published;
		
		return count($where) ? ' WHERE '.implode(' AND ', $where) : '';
	}

	function _buildOrder()
	{
		$filter_order = $this->getState('order');
		if (!$filter_order)
			$filter_order = 'ctime';
		
		$filter_order_Dir = $this->getState('orderDir');

		$orderBy = "ORDER BY ".$filter_order." ".$filter_order_Dir;
		
		return $orderBy;
	}
}