<?php

class ExGlobals
{
	public static $globals = array();
	public static $cookie = "";
	
	private static $cgi_parameters = array();
	
	//
	public static function init()
	{
		if (array_key_exists('QUERY_STRING', $_SERVER) && $_SERVER['QUERY_STRING'] != '')
			parse_str($_SERVER['QUERY_STRING'], self::$globals);
		
		//lets parse slash based parameters
		$path_uri = (array_key_exists('PATH_INFO', $_SERVER)) ? $_SERVER['PATH_INFO'] : $_SERVER['REQUEST_URI'];
		if ($path_uri != '')
		{
			
			
			//
			
			//lets remove realtive folder
			
			
			$params = explode('/', $path_uri);
			if (isset($params[0]) && $params[0] == '') 
				array_shift($params);
				
			//lets compare if first value is actually a relative folder
			if(isset($params[0]))
			{
				$rel_path = EX_REL_PATH;
				if($rel_path == $params[0].'/')
				{
					array_shift($params);
					//now lets check if next folder is an actual folder
					if(isset($params[0]) && is_dir($params[0]))
						array_shift($params);
				}
				
			}
						
			
			//the first parameters is always a url key or uuid
			if(isset($params[0]) && trim($params[0]) != '')
			{
				self::set('eX_global_url_key', preg_replace('/[^a-zA-Z0-9_-]/i', '-', $params[0]));
				array_shift($params);
			}
			//lets find method as a second parameter
			if(isset($params[0]) && trim($params[0]) != '')
			{
				self::set('eX_global_method', preg_replace('/[^a-zA-Z0-9_-]/i', '-', $params[0]));
				array_shift($params);
			}
			
			if(count($params) > 0)	
				for ($i = 0; $i < count($params); $i = $i + 2) 
				{
					if(!isset($params[$i]) || !isset($params[$i+1]) || trim($params[$i]) == '') break;
					array_push(self::$cgi_parameters, $params[$i+1]);
					self::$globals[preg_replace('/[^a-zA-Z0-9_-]/i', '_', $params[$i])] = (isset($params[$i+1])) ? $params[$i+1] : NULL;
				}				
				
				
		} 
		
		//lets escape global arrays
		if(isset($_POST))
			array_walk($_POST, array('ExGlobals', 'escape_parameters'));		
		if(isset($_GET))
			array_walk($_GET, array('ExGlobals', 'escape_parameters'));
		if(isset($_REQUEST))
			array_walk($_REQUEST, array('ExGlobals', 'escape_parameters'));
		
		array_walk(self::$globals, array('ExGlobals', 'escape_parameters'));		
		self::$globals = array_merge(self::$globals, $_POST);
		
		//define variable to actual instances including key
		defined('EX_PHP_SELF_URL') or define('EX_PHP_SELF_URL', EX_PHP_URL . ExGlobals::get('eX_global_url_key') . '/');
		
		//lets set site uuid into cookie
		/*if(ExGlobals::has('s') && ExUuid::is_uuid(ExGlobals::get('s'))) 
		{
			setcookie("EX_SITE_UUID", ExGlobals::get('s'), time()+(3600*24*1000), "/");
			defined('EX_SITE_UUID') or define('EX_SITE_UUID', ExGlobals::get('s'));
		}
		else if(isset($_COOKIE['EX_SITE_UUID']))
		{
			defined('EX_SITE_UUID') or define('EX_SITE_UUID', $_COOKIE['EX_SITE_UUID']);
		}
		else
		{
			defined('EX_SITE_UUID') or define('EX_SITE_UUID', NULL);
		}*/
	
		
	}
	
	private function escape_parameters(&$val, &$key)
	{
		// Encode key
		$key = htmlspecialchars(utf8_decode($key), ENT_COMPAT, 'UTF-8');

		// Handle array of values (php does this automatically for post vars named like "varname[]")
		if (is_array($val)) {
			array_walk($val, array('ExGlobals', 'escape_parameters'));
			return;
		}

		// Strip html unless form has been verified
		//if(FALSE == $this->verify())
			//$sValue = strip_tags($sValue);

		// Encode value
		$val = stripslashes($val);

		// Prevent sql injection
		$val = preg_replace('/\bexec\s+(?:sp|xp)\w+|\bdeclare\s+@\w+|\bset\s+@\w+|\bcast\s*\((?:0x[0-9a-f]|\d{6,})|\bunion\s+select\b/i', '##INVALID##', $val);
	}
	
	
	//
	public static function get_site()
	{
		/*
		if(self::has('eX-site'))
			return self::get('eX-site')
		else
			if($inst = self::get_requested_instance()) 
			{
				$sites = eXQuerySelect::ancestors($inst->get_instance_uuid(), EX_UUID_TYPE_SITE);
				if($sites && count($sites) > 0)
					self::set('eX-site', eXInstance::);
			}	*/	
	}
	
	//
	public static function get_site_uuid()
	{
		if(self::has('eX-site-uuid'))
			return self::get('eX-site-uuid');
		else if(($inst = self::get_requested_instance()) instanceof eXInstance) 
		{
			$sites = eXQuerySelect::ancestors($inst->get_instance_uuid(), EX_UUID_TYPE_SITE);
			if($sites && count($sites) > 0)
			{
				self::set('eX-site-uuid', $sites[0]['instance_uuid']);
				return $sites[0]['instance_uuid'];
			}
		}	
		return NULL;	
	}
	
	//
	public static function get_requested_uuid()
	{
		$inst = self::get('eX_requested_instance');
		return ($inst && $inst instanceof eXInstance) ? $inst->get_instance_uuid() : NULL;
	}
	
	//
	public static function get_requested_instance()
	{
		return (self::has('eX_requested_instance') && self::get('eX_requested_instance') instanceof eXInstance) ? self::get('eX_requested_instance') : NULL;
	}
	
	//
	public static function get_url_key()
	{
		return (self::has('eX_global_url_key')) ? self::get('eX_global_url_key') : NULL;
	}
	
	//
	public static function get_controller()
	{
		return self::get_url_key();
	}
	
	//
	public static function get_method()
	{
		return (self::has('eX_global_method')) ? self::get('eX_global_method') : 'run';//run is a default
	}
	
	//
	public static function get_cgi_parameters()
	{
		return self::$cgi_parameters;
	}
	
	
	//
	public static function has($key)
	{
		return array_key_exists($key, self::$globals);
	}
	
	
	//
	public static function get($key)
	{
		if (self::has($key))		
			return self::$globals[$key];		
		else 
			return NULL;
	}
	
	//
	public static function set($key, $data = NULL)
	{
		if(is_array($key)) 
			foreach($key as $kkey => $val)
				self::$globals[$kkey] = $val;
		else
			self::$globals[$key] = $data;
	}
	
	//
	public static function set_cookie($key, $val = NULL)
	{
		$currentarr = self::get_cookie();		
		$currentarr[$key] = $val;		
		self::$cookie = http_build_query($currentarr);
		@setcookie("EX_PRIVATE_DATA", self::$cookie, time()+(3600*24*1000), "/");
	}
	
	//
	public static function get_cookie($key = NULL)
	{
		self::$cookie = (self::$cookie != "") ? self::$cookie : (isset($_COOKIE['EX_PRIVATE_DATA']) ? $_COOKIE['EX_PRIVATE_DATA'] : '');
		parse_str(self::$cookie, $currentarr);
		return (!$key) ? $currentarr : (isset($currentarr[$key])	? $currentarr[$key] : NULL);
	}
		
	//
	public static function remove($key)
	{
		if (self::has($key))
		{
			unset(self::$globals[$key]);
			return TRUE;		
		}
		else
			return FALSE;
	}
	
}

