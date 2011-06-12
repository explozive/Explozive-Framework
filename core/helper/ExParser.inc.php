<?php

class ExParser
{	
	const REGEX_PROPERTIES = '/<!--\s*eX-insert-property\s*:\s*([a-zA-Z0-9_]+)\s*-->|\{\s*([a-zA-Z0-9_]+)\s*\}/i';
	//const REGEX_INSTANCES = '/<!--\s*eX-insert-instance\s*:\s*([a-zA-Z0-9_-\s]+)\s*-->|\{\s*([a-zA-Z0-9_-\s]+)\s*\}/i';
	const REGEX_INSTANCES = '/<!--\s*eX-insert-instance\s*:\s*([a-zA-Z0-9_-\s]+)\s*-->|\{\s*insert-instance\s*:\s*([a-zA-Z0-9_-\s]+)\s*\}/i';
	//
	public static function properties(ExInstance $instance, $content)
	{		
		//if($instance->get_reference() instanceof ExInstance)
			//$instance = $instance->get_reference();
		//$instance->dump();
		//$iproperties = $instance->get_properties();
		
		$iproperties = $instance->get_runtime('properties');
		
		preg_match_all(self::REGEX_PROPERTIES, $content, $properties, PREG_SET_ORDER);
				
		if($properties && count($properties) > 0)
			foreach($properties as $props)
			{				
				$key = trim(array_pop($props));	
				$val = (isset($iproperties[$key]))
									? $iproperties[$key]
									: call_user_func(array($instance, ((substr($key, 0, 5) == 'abbr_' || substr($key, 0, 4) == 'ini_')) ? $key : ('get_' . $key)))
								;
				
				$content = str_replace($props[0], ($val && is_string($val)) ? $val : '', $content);
			}
		return $content;
	}
	
	//
	public static function abbr($content)
	{		
		$content = str_replace(array('<!--', '-->'), array('&lt;!--', '--&gt;'), $content);
		
		if(($pos = stripos(strip_tags($content), "&lt;!--more--&gt;")) != FALSE)		 
		{				
			$content = ExString::substr($content, $pos-6, NULL, TRUE, TRUE);//must be -6 cause of the < to &lt; replacement
		}
		return str_replace(array('&lt;!--', '--&gt;'), array('<!--', '-->'), $content);
	}
	
	
	//
	public static function instances(eXInstance $instance, $content)
	{		
		$instances = array();
		$assigned = $instance->get_eX_assigned(eXInstance::GET_VALUE_AS_ARRAY, 'type==\'eX-insert-instance\'');
		preg_match_all(self::REGEX_INSTANCES, $content, $properties, PREG_SET_ORDER);
		
		if($properties && count($properties) > 0)
			foreach($properties as $props)
			{				
				$key = trim(array_pop($props));
				//lets look if any for them are pre-assigned via the database
				if(ExUuid::is_uuid($key) == FALSE && 
					 count($assigned) > 0 && ($assign = ExArray::array_search($assigned, 'value==\'' . $key . '\''))
					)					
					$key = $assign[0]['instance_uuid'];

				array_push($instances, array('property' => $props[0], 'value' => $key));	
				/*if(ExUuid::is_uuid($key))
				{
					$new = eXInstance::init($key);	
					$new->set_properties($instance);	
					$new->set_reference($instance);		
					$content = str_replace($props[0], $new->process(), $content);									
				}	*/	
			}
		return $instances;
	}
	
	public static function log_inline(eXInstance $instance, $content)
	{
		$uuid = ExUuid::create_uuid();
		return eXLog::log_process("Processing '" . $instance->get_instance_name() . "'", eXLog::INSTANT) . 
			"<!-- eX-inline-edit id=\"" . $uuid . "\" instance_name=\"" . $instance->get_instance_name() . "\" instance_type=\"" . $instance->get_type_name() . "\" instance_uuid=\"" . $instance->get_instance_uuid() . "\" -->" .
			$content . 
			"<!-- eX-inline-edit-end id=\"" . $uuid . "\" -->" .
			eXLog::log_process("Ending processing '" . $instance->get_instance_name() . "'", eXLog::INSTANT)
		;
	}
	
	public static function parse_url_key($content)
	{
		///\/*\bindex\.\w{2,6}([\/?]+)([^\s\'"<>]*(?:ci_uuid|ci_id)[\/=](?:(?!&quot;|&apos;|&gt;|&lt;)[^\s\'"<>])*)/
		preg_match_all('/\/([0-9a-f]{8}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{12})/i', $content, $uuids, PREG_SET_ORDER);
				
		$uuids = array_unique(ExArray::array_to_numeric($uuids, 1));
		
		if(count($uuids) > 0)
		{
			$keys = eXQueryInstance::uuid_to_key($uuids);		
			foreach($keys as $uuid => $key)
				$content = str_replace("/$uuid", "/$key", $content);

			/*preg_replace_callback(
				'/\/([0-9a-f]{8}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{12})/i', 
				create_function(
            // single quotes are essential here,
            // or alternative escape all $ as \$
            '$matches',
            'return strtolower($matches[0]);'
        ), $content, $uuids, PREG_SET_ORDER);*/
			
		}
		
		return $content;
	}
	
	
	public static function parse_ini($sConfigData, $sAllowNewKeysRegex = NULL)
	{
		$aFinal = array();
		$aDefault = array();
		$sAllowNewKeysRegex = '/./';

		$aLines = preg_split('/\x0d\x0a|\x0d|\x0a/', $sConfigData);
		while ($aLines) {
			$sLine = array_shift($aLines);

			// Comment or empty line
			if (preg_match('!^\s*(?:#|//|$)!', $sLine))
				continue;

			// Get name and value
			if (!preg_match('/^\s*([^=]+?)\s*(?:=\s*(.+?)\s*)?$/', $sLine, $aMatches))
				continue;
			$sName = $aMatches[1];
			$sValue = $aMatches[2];

			// Check whether to accept key
			if (!array_key_exists($sName, $aDefault) && $sAllowNewKeysRegex !== TRUE
						&& (!is_string($sAllowNewKeysRegex) || !preg_match($sAllowNewKeysRegex, $sName))
			) {
				continue;
			}

			// Handle empty value
			if ($sValue == '' || $sValue == '""' || $sValue == '\'\'') {
				$sValue = '';
			}

			// Handle ' or " quoted value
			elseif ((substr($sValue, 0, 1) == '"' && substr($sValue, -1, 1) == '"')
					|| (substr($sValue, 0, 1) == '\'' && substr($sValue, -1, 1) == '\'')
			) {
				$sValue = substr($sValue, 1, -1);
			}

			// Handle multiline " quoted value
			elseif (substr($sValue, 0, 1) == '"') {
				do {
					$sValue .= PHP_EOL . array_shift($aLines);
				} while (substr($sValue, -1, 1) != '"');
				$sValue = substr($sValue, 1, -1);
			}

			// Handle special values (unquoted only)
			elseif (strtoupper($sValue) == 'TRUE')
				$sValue = TRUE;
			elseif (strtoupper($sValue) == 'FALSE')
				$sValue = FALSE;
			elseif (strtoupper($sValue) == 'NULL')
				$sValue = NULL;

			// Set config key
			$aFinal[$sName] = $sValue;
		}
		return $aFinal;
	}
	
}