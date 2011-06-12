<?php  if(!defined('EX_VERSION')) exit('Access denied!');
/**
 * Copyright (C) 2011  Explozive.com
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Explozive to newer
 * versions in the future. If you wish to customize it for your
 * needs please refer to http://www.explozive.com/api/documentation for more information.
 */

 /**
 * @name ExploziveFramework
 * @version 2.0
 */
 
 /**
 * This class adds missing and simplifies some methods to help us working with arrays
 *
 * @name ExArray
 * @package ExploziveFramework
 * @subpackage Helper
 * @author exceo <exceo@explozive.com>
 * @link http://www.explozive.com/api/documentation/article/helper-array
 * @example ../../example.com/controller/HelperArray.inc.php
 */
 
class ExArray
{	
	
	/**
	 * method searches multi-dimensional array using expression
	 *
	 * @param	string $array multi-dimensional array
	 * @param	string $expression expression, e.g. "shape=='circle' AND color=='blue'"
	 * @param	bool $as_indexes type of the returned value, true - returns an array of indexes, false - complete elements 
	 * @return array
	 * @link http://www.explozive.com/api/documentation/article/helper-array#array_unique
	 */	
	public static function array_search($array, $expression, $as_indexes = false) {
		if($expression == '')
			return $array;
		if (!is_array($array) || !preg_match('/[a-z_][\w_]*(?:==|!=|>|<|>=|<=).+/', $expression))
			return null;
		$result = array();
		$expression = preg_replace("/([^\s]+?)(=|<|>|!)/", "\$a['$1']$2", $expression);
		foreach ( $array as $key => $a ) {
			if (eval( "return $expression;"))
				$result[] = ($as_indexes) ? $key : $a;
		}
		return (count($result) > 0) ? $result : null;
	}
	
	
	
	
	/**
	 * method parses multi-dimensional array removing duplicated values
	 *
	 * @param	string $array multi-dimensional array
	 * @return array
	 * @link http://www.explozive.com/api/documentation/article/helper-array#array_unique
	 */	
	public static function array_unique($array) 
	{		
		array_walk($array, create_function('&$value,$key', '$value = json_encode($value);'));
		$array = array_unique($array);
		array_walk($array, create_function('&$value,$key', '$value = json_decode($value, true);'));
		return $array;	
	}
	
	/**
	 * method converts any value of multi-dimensional array to numeric array
	 *
	 * @param	string $array multi-dimensional array 
	 * @param	int $index the index number of the value to move to numeric array
	 * @return array
	 * @link http://www.explozive.com/api/documentation/article/helper-array#array_to_numeric
	 */	
	public static function array_to_numeric($array, $index = 0) 
	{		
		$newarray = array();
		foreach($array as $element)
			if(isset($element[$index]))
				array_push($newarray, $element[$index]);
						
		return $newarray;	
	}
	
	
	/**
	 * method converts any first 2 values of multi-dimensional array to assoc array (key[0] => value[1])
	 *
	 * @param	string $array multi-dimensional array 
	 * @param	int $index the index number of the value to move to numeric array
	 * @return array
	 * @link http://www.explozive.com/api/documentation/article/helper-array#array_to_assoc
	 */	
	public static function array_to_assoc($array) 
	{		
		$newarray = array();
		foreach($array as $element)
			if(($current = current($element)) && ($next = next($element)))
				$newarray[$current] = $next;
						
		return $newarray;	
	}
	
}