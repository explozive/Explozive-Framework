<?php
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
 * This class adds missing and simplifies some methods to help us working with arrays
 *
 * @name ExArray
 * @version 1.0
 * @package ExploziveFramework
 * @subpackage Helper
 * @access public
 * @author CDC <cdc@explozive.com>
 * @link http://www.explozive.com/api/documentation/article/
 */

class ExArray
{
	
	const CSV_FILE = 0;
	const CSV_TEXT = 1;
	
	public static function csv_to_array($source, $delimiter=',', $type = self::CSV_FILE)	
	{
	  if($type == self::CSV_FILE && (!file_exists($filename) || !is_readable($filename)))
	      return FALSE;		
		else if($type == self::CSV_TEXT)  
		{
			$handle = fopen("php://memory", "rw");
			fwrite($handle, $source);	 
			fseek($handle, 0);
	  }	
		else
			$handle = fopen($source, 'r');
		
		$header = NULL;
	  $data = array();
	
	  if ($handle !== FALSE)
	  {
      while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE)
      {
				if(!$header)
					$header = $row;
				else
					$data[] = array_combine($header, $row);
      }
	    fclose($handle);
	  }
	  return $data;
	}
	
	
	public static function array_unique($array) 
	{		
		array_walk($array, create_function('&$value,$key', '$value = json_encode($value);'));
		$array = array_unique($array);
		array_walk($array, create_function('&$value,$key', '$value = json_decode($value, true);'));
		return $array;	
	}
	
	public static function array_assoc_to_numeric($array, $index = 0) 
	{		
		$newarray = array();
		foreach($array as $element)
			if(isset($element[$index]))
				array_push($newarray, $element[$index]);
						
		return $newarray;	
	}
	
	public static function array_to_assoc($array) 
	{		
		$newarray = array();
		foreach($array as $element)
			if(($current = current($element)) && ($next = next($element)))
				$newarray[$current] = $next;
						
		return $newarray;	
	}
	
	public static function array_search($array, $expression, $as_indexes = FALSE) {
		if($expression == '')
			return $array;
		if (!is_array($array) || !preg_match('/[a-z_][\w_]*(?:==|!=|>|<|>=|<=).+/', $expression))
			return NULL;
		$result = array();
		$expression = preg_replace("/([^\s]+?)(=|<|>|!)/", "\$a['$1']$2", $expression);
		foreach ( $array as $key => $a ) {
			if (eval( "return $expression;"))
				$result[] = ($as_indexes) ? $key : $a;
		}
		return (count($result) > 0) ? $result : NULL;
	}
	
		
	
	public static function array_to_json($arr)
	{ 
    if(function_exists('json_encode')) return json_encode($arr); //Lastest versions of PHP already has this functionality.
    $parts = array(); 
    $is_list = false; 

    //Find out if the given array is a numerical array 
    $keys = array_keys($arr); 
    $max_length = count($arr)-1; 
    if(($keys[0] == 0) and ($keys[$max_length] == $max_length)) {//See if the first key is 0 and last key is length - 1 
        $is_list = true; 
        for($i=0; $i<count($keys); $i++) { //See if each key correspondes to its position 
            if($i != $keys[$i]) { //A key fails at position check. 
                $is_list = false; //It is an associative array. 
                break; 
            } 
        } 
    } 

    foreach($arr as $key=>$value) { 
        if(is_array($value)) { //Custom handling for arrays 
            if($is_list) $parts[] = self::array2json($value); /* :RECURSION: */ 
            else $parts[] = '"' . $key . '":' . self::array2json($value); /* :RECURSION: */ 
        } else { 
            $str = ''; 
            if(!$is_list) $str = '"' . $key . '":'; 

            //Custom handling for multiple data types 
            if(is_numeric($value)) $str .= $value; //Numbers 
            elseif($value === false) $str .= 'false'; //The booleans 
            elseif($value === true) $str .= 'true'; 
            else $str .= '"' . addslashes($value) . '"'; //All other things 
            // :TODO: Is there any more datatype we should be in the lookout for? (Object?) 

            $parts[] = $str; 
        } 
    } 
    $json = implode(',',$parts); 
     
    if($is_list) return '[' . $json . ']';//Return numerical JSON 
    return '{' . $json . '}';//Return associative JSON 
	} 
	
}