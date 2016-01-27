<?php
/**
 * $Id: jd_expression_evaluator.php 7311 2011-08-19 11:18:02Z shitz $
 * $LastChangedDate: 2011-08-19 17:18:02 +0600 (Fri, 19 Aug 2011) $
 * $LastChangedBy: shitz $
 * MightyDefender by Mighty Extensions
 * a component for Joomla! 1.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mightyextensions.com/
 * @copyright Copyright (C) 2006 - 2011 MightyExtensions (http://www.mightyextensions.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/
defined ('_JEXEC') or die("Restricted Access");

/**
 * Class that evaluates boolean expressions.
 *  
 * @author nurlan
 *
 */
class JD_Expression_Evaluator extends JObject
{
	var $rules;
	var $operations;
	var $values;
	var $lastResult;
	var $results;
	
	function __construct() {
		parent::__construct();
		
		$this->rules 		= array();
		$this->operations 	= array();
		$this->values 		= array();
		$this->results 		= array();
	}
	
	/**
	 * Adds a rule for evaluation. You can pass JD_Expression_Evaluator instances as rules for multiple level 
	 * rule evaluation (similar to parathesis in logical expressions)
	 * @param $rule JD_Rule|JD_Expression_Evaluator
	 * @param $operation string 
	 * @param $value The value to test
	 */
	function addRule($rule, $operation = '', $value = '') {
		$this->rules [] 		= $rule;
		$this->values [] 		= $value;
		
		// The first operand does not have an operation
		if (!count($this->operations))
			$this->operations [] 	= '';
		else {
			if (!in_array($operation, array('and', 'or'))) {
				JError::raiseWarning(404, 'Bad opeator for JD_Expression_Evaluator::addRule');
				return false;
			}
			$this->operations [] 	= JString::strtolower($operation);
		}
		
		return true;
	}
	
	/**
	 * Evaluate the rules (Can add multiple level rule checking, by using composite pattern, but don't need for now)
	 * @return boolean
	 */
	function evaluate() {
		if (empty($this->rules)){
			$this->lastResult = null;
			return null;
		}
			
		// This place can be optimized a bit :)
		foreach ($this->rules as $k => $rule) 
		{
			if (strtolower(get_class($rule)) == 'jd_expression_evaluator') {
				$this->results[ $k ] = $rule->evaluate();
			}
			else
				$this->results[ $k ] = $rule->check($this->values[ $k ]);
		}
		
		if (empty($this->results)) {
			$this->lastResult = null;
			return null;
		}
		
		$expression = implode('|', $this->operations) .'|';
		
		// Exceptional case
		if ($expression == 'or|')
			$ors = array('');
		else
			$ors = explode('or|', $expression);
		
		
		$iResult = 0;
		// optimization here: if any operand of 'or' opeator is true, the whole expression evaluates to 'true' (marked as * )
		foreach ($ors as $part) {
			$ands = explode('|', trim($part, ' |'));
			$ands = array_filter($ands, array(&$this, '_filterArray'));
			if (!count($ands))
				continue;
						
			$andResult = true;

//			var_dump($ands);
			// optimization here: if any operand of 'and' opeator is false, the whole expression evaluates to 'false' (marked as **)
			for ($i = 0, $c = count($ands); $i <= $c; $i++)
			{
//				echo $iResult, ' = ', $this->results[ $iResult ], "\n";
				// **
				if (!$this->results[ $iResult ]) {
					$andResult = false;
					$iResult += $c - $i + 1;
					break;
				}
				$iResult++;
			}
			
			// *
			if ($andResult === true) {
				$this->lastResult = true;
				return true;
			}
		}
		
		// Last 'or' occurence exception
		if ('or' == end($this->operations) && end($this->results)) {
			$this->lastResult = true;
			return true;
		}
		
		// If we have only 'ors' :)
		if (!in_array('and', $this->operations)) 
		{	
			$this->lastResult = in_array(true, $this->results);
			return $this->lastResult;
		}
		
		$this->lastResult = false;
		return false;
	}
	
	function getLastResult() {
		if (!is_null($this->lastResult))
			return $this->lastResult;
	}
	
	function _filterArray($value) {
		return !empty($value) && in_array($value, array('and', 'or'));
	}
}