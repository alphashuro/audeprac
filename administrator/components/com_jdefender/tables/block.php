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
defined('_JEXEC') or die('Restricted Access');

/**
 * JDefender Block Table class
 *
 * @package		Joomla
 * @subpackage	JDefender
 * @since 1.0
 */
class TableBlock extends JTable {
	var $id; //int
	var $type = null; //string
	var $value = null; //string
	var $ctime;
	var $reason;
	var $published;
	
	function __construct(& $db) {
		parent::__construct('#__jdefender_block_list', 'id', $db);
	}
	
	function loadByTypeAndValue($type, $value) {
		$db = & JFactory::getDBO();
		
		$db->setQuery("SELECT * from #__jdefender_block_list WHERE `type`=".$db->Quote($type)." AND `value`= ".$db->Quote($value).' LIMIT 1');
		
		$res = $db->loadObject();
		
		if ($res) {
			return $this->bind($res);
		}
		
		return false;
	}
	
	function check() {
		if (empty($this->type)) {
			$this->setError(JText::_('Block type is empty'));
			return false;
		}
		
		if (empty($this->value)) {
			$this->setError(JText::_('Block value is empty'));
			return false;
		}
		
		$db = & JFactory::getDBO();
		
		switch ($this->type) {
			case 'ip':
				if (strpos($this->value, '-') !== false) {
					$range = explode('-', $this->value);
					
					if (count($range) != 2) {
						$this->setError(JText::_('Bad IP range').': '.$this->value);
						return false;
					}
					
					$from 	= ip2long(trim($range[ 0 ]));
					$to 	= ip2long(trim($range[ 1 ]));
					
					if ($from == -1 || $from === false) {
						$this->setError(JText::_('Invalid IP Address').': "'.$range[ 0 ].'" '.JText::_('in range').' - '.$this->value);
						return;
					}

					if ($to == -1 || $to === false) {
						$this->setError(JText::_('Invalid IP Address').': "'.$range[ 1 ].'" '.JText::_('in range').' - '.$this->value);
						return;
					}
					
					$this->value = $range[ 0 ].' - '.$range[ 1 ];
				}
				else {
					$ip2long = ip2long($this->value);
					if ($ip2long == -1 || $ip2long === false) {
						$this->setError(JText::_('Invalid IP Address').': '.$this->value);
						return;
					}
				}
				break;
			case 'user':
			case 'login':
				$db->setQuery('SELECT COUNT(*) FROM #__users WHERE id = '.(int)$this->value);
				if ( ! $db->loadResult()) {
					$this->setError(JText::_('User does not exist').'. ID = '. $this->value);
					return false;
				}
				break;
			case 'referer':
				$uri = & JURI::getInstance($this->value);
				
				if (!$uri->getScheme() || !$uri->getHost()) {
					$this->setError(JText::_('Bad referer URL').': '.$this->value.' - '.JText::_('URL must start with "http://" or "https://"'));
					return false;
				}
				break;
		}
		
		if (empty($this->id))
		{
			$db->setQuery("SELECT COUNT(*) from #__jdefender_block_list WHERE `type`=".$db->Quote($this->type)." AND `value`= ".$db->Quote($this->value));
			
			if ($db->loadResult()) {
				if ($this->type == 'ip')
					$type = 'IP Address';
				else
					$type = $this->type;
				
				$this->setError(JText::_('Duplicate record').'. '.JText::_('Type').': '.$type.', '.JText::_('Value').': '.$this->value);
				return false;
			}
		}
		
			
		
		return parent::check();
	}
}
