<?php

class ExJson
{
	
		
	public static function encode($arr, $const = NULL)
	{ 
    if(function_exists('json_encode')) return json_encode($arr, JSON_HEX_QUOT | JSON_HEX_APOS); //Lastest versions of PHP already has this functionality.
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
            if($is_list) $parts[] = self::Encode($value); /* :RECURSION: */ 
            else $parts[] = '"' . $key . '":' . self::Encode($value); /* :RECURSION: */ 
        } else { 
            $str = ''; 
            if(!$is_list) $str = '"' . $key . '":'; 

            //multiple data types 						
            if(is_numeric($value)) 
							$str .= $value; //Numbers 
            elseif($value === false) 
							$str .= 'false'; //The booleans 
            elseif($value === true) 
							$str .= 'true'; 
            elseif($value === null) 
							$str .= 'null'; 
						else 
							$str .= '"' . addslashes($value) . '"'; //All other things 

            $parts[] = $str; 
        } 
    } 
    $json = implode(',',$parts); 
     
    if($is_list) return '[' . $json . ']';//Return numerical JSON 
    return '{' . $json . '}';//Return associative JSON 
	} 
	
	
	public static function decode($str) 
	{
	
		$str = str_replace("\r\n", "\n", $str);
    $str = str_replace("\r", "\n", $str);
    // JSON requires new line characters be escaped
    $str = str_replace("\n", "\\n", $str);
		$str = str_replace("\t", "\\t", $str);
		
		
		if(function_exists('json_decode')) 
		 	return json_decode($str, TRUE);
		
		$arr = array('index' => array(), 'waarde' => array());
		$out = array();
		
		preg_match_all('/\{([\s\S]*)\}/i', $str, $result, PREG_OFFSET_CAPTURE);
		
		var_dump($result);
		die;
		
		if(preg_match('/^\{([\s\S]*)\}$/i', $str, $result))
		{
			$str = $result[1];
				
		
		//$str = str_replace('{', '', $str);
		//$str = str_replace('}', '', $str);
		$str = str_replace('"', '', $str);
				
			$dirty = explode(',', $str);
			
			foreach($dirty as $set) 
			{
		    $pair = explode(':', $set);
				$value = $pair[1];	   
				var_dump($value);			
				if(is_numeric($value)) 
					$value = intval($value); //Numbers 
				elseif($value === 'false') 
					$value = false; //The booleans 
				elseif($value === 'true') 
					$value = true;
				elseif($value === 'null') 
					$value = null;
				elseif(preg_match('/^\[\{([\s\S]*)\}\]$/i', $value, $result)) //[^]]
					var_dump($value);
					//$value = self::Decode($result[1]);
				
				$out[$pair[0]] = $value;
			}
		
		}
		
		return $out;
	}
		
}