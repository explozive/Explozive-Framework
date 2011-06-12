<?php

/*
* This constant defines whether the application should be in debug mode or not. Defaults to FALSE.
*/

defined('EX_VERSION') or define('EX_VERSION', '2.0');

defined('EX_ABS_PATH') or define('EX_ABS_PATH', dirname(__FILE__) . '/');
defined('EX_ABS_PATH_PUBLIC') or define('EX_ABS_PATH_PUBLIC', '/home/explozive/www/');

//defined('EX_RELATIVE_FOLDER') or define('EX_RELATIVE_FOLDER', '/');// /eX
defined('EX_RELATIVE_PATH') or define('EX_RELATIVE_PATH', '/'); // 		/eX/


defined('EX_FRAMEWORK_ABS_PATH') or define('EX_FRAMEWORK_ABS_PATH', dirname(__FILE__) . '/');

//relative path
defined('EX_PHP_URL') or define('EX_PHP_URL', EX_RELATIVE_PATH . 'index.php/');

defined('EX_PATH_SITE') or define('EX_PATH_SITE', EX_REL_ROOT . EX_SITE_FOLDER . '/');
defined('EX_PATH_CONTROLLER') or define('EX_PATH_CONTROLLER', EX_PATH_SITE . 'controller/');
defined('EX_PATH_LIBRARY') or define('EX_PATH_LIBRARY', EX_PATH_SITE . 'library/');
defined('EX_PATH_VIEW') or define('EX_PATH_VIEW', EX_PATH_SITE . 'view/');
defined('EX_PATH_CACHE') or define('EX_PATH_CACHE', EX_PATH_SITE . 'cache/');
defined('EX_PATH_STORAGE') or define('EX_PATH_STORAGE', EX_PATH_SITE . 'storage/');

defined('EX_PHP_FILE_EXT') or define('EX_PHP_FILE_EXT', 'inc.php');

defined('EX_PATH_CORE_HELPER') or define('EX_PATH_CORE_HELPER', EX_FRAMEWORK_ABS_PATH . 'helper/');
defined('EX_PATH_CORE_EXCEPTIONS') or define('EX_PATH_CORE_EXCEPTIONS', EX_FRAMEWORK_ABS_PATH . 'exceptions/');

defined('EX_PATH_PUBLIC') or define('EX_PATH_PUBLIC', '/' . EX_REL_PATH . EX_SITE_FOLDER . '/public/');
defined('EX_PATH_COMMUNITY') or define('EX_PATH_COMMUNITY', EX_PATH_PUBLIC . 'community/');
defined('EX_PATH_CSS') or define('EX_PATH_CSS', EX_PATH_PUBLIC . 'css/');
defined('EX_PATH_JS') or define('EX_PATH_JS', EX_PATH_PUBLIC . 'javascript/');
defined('EX_PATH_IMAGES') or define('EX_PATH_IMAGES', EX_PATH_PUBLIC . 'images/');


/*
* This constant defines whether the application should be in debug mode or not. Defaults to FALSE.
*/
defined('EX_DEBUG') or define('EX_DEBUG', TRUE);

defined('EX_LOGS_ABS_PATH') or define('EX_LOGS_ABS_PATH', dirname(__FILE__) . '/logs/');

defined('EX_CACHE_ABS_PATH') or define('EX_CACHE_ABS_PATH', dirname(__FILE__) . '/cache/');

defined('EX_COMMUNITY') or define('EX_COMMUNITY', dirname(__FILE__) . '/community/');
defined('EX_PUBLIC_COMMUNITY') or define('EX_PUBLIC_COMMUNITY', 'public/community/');
defined('EX_URL_COMMUNITY') or define('EX_URL_COMMUNITY', EX_RELATIVE_PATH . 'public/community/');
defined('EX_PUBLIC_FILES') or define('EX_PUBLIC_FILES', 'public/files/');

defined('EX_STORAGE_ABS_PATH') or define('EX_STORAGE_ABS_PATH', dirname(__FILE__) . '/storage/');
defined('EX_STORAGE_PUBLIC_ABS_PATH') or define('EX_STORAGE_PUBLIC_ABS_PATH', EX_ABS_PATH . 'public/storage/');


/* Types	*/
define('EX_UUID_TYPE_INSTANCE_TYPE',	'1ab7dacc-4217-b10c-c849-5b6e7029cfe2');
define('EX_UUID_TYPE_FOLDER',					'01350189-01b3-0de9-095d-05ec0592409f');
define('EX_UUID_TYPE_LABEL',					'9ea4d1f4-9dd4-1324-5264-16d453c45ac4');
define('EX_UUID_TYPE_PHP_PROCESSOR',	'5a345f24-95a4-d004-18e4-d8a4db441244');
define('EX_UUID_TYPE_CONTROLLER',				'5a345f24-95a4-d004-18e4-d8a4db441244');

define('EX_UUID_TYPE_STYLESHEET',			'd79416e4-13a4-9bb4-59f4-1af49b445c14');
define('EX_UUID_TYPE_JAVASCRIPT',			'a84b14ed-5986-059e-32e2-72bb689b2625');
define('EX_UUID_TYPE_HTML', 					'9ca49b64-1a94-93f4-5b04-9f941f245f84');
define('EX_UUID_TYPE_TEXT', 					'183f7fbb-171a-81d8-2224-1871a4bd7c18');
define('EX_UUID_TYPE_MEMBER', 				'9c74d154-5264-1dc4-d324-94d418349924');
define('EX_UUID_TYPE_DOMAIN', 				'eed53f32-3aa2-af01-060f-c58bb64f9dea');
define('EX_UUID_TYPE_MENU', 					'01a06e32-1026-4f5f-68fc-c0a5780658c2');
define('EX_UUID_TYPE_MENU_ITEM', 			'd7f330dd-1481-87f9-d553-8a69d99f84be');
define('EX_UUID_TYPE_SITE', 					'1c641a64-1254-5db4-d2b4-5d441ac410c4');
define('EX_UUID_TYPE_DOCUMENT', 			'5b641a24-12c4-1c94-58f4-d6c4d3041374');
define('EX_UUID_TYPE_INVOICE', 				'c027d9f5-7812-68ae-6e60-eaa4073adfde');
define('EX_UUID_TYPE_COLLECTION', 		'028156b5-78a1-196d-d264-136850f40dcc');
define('EX_UUID_TYPE_VIEW', 					'2cd73b4c-2170-72aa-f0f1-aae7b1c47c9d');
define('EX_UUID_TYPE_ADVERTISING', 		'9fb7fdb2-1bbd-d02e-195f-dd9e471e479d');
define('EX_UUID_TYPE_VIDEO', 					'0fa6930f-9d50-91b4-40c8-246cc1e179d2');
define('EX_UUID_TYPE_PRODUCT', 				'ed212136-9730-2cc9-19b4-6e68c3e96eef');
define('EX_UUID_TYPE_ORDER', 					'b81089af-470e-e3b6-fef3-110dafb8fe57');
define('EX_UUID_TYPE_TRANSACTION',		'2e5ec9c5-b54c-c831-ae9d-99b0d2fa24a7');


class Ex 
{
	private static $class_dirs = array(
		EX_PATH_LIBRARY,
		EX_FRAMEWORK_ABS_PATH,
		EX_PATH_CORE_HELPER,
		EX_PATH_CORE_EXCEPTIONS,
		EX_ABS_PATH
		);
		
	const EX_DISABLED = FALSE;
	const EX_ENABLED 	= TRUE;
	
	/**
	* init framework
	*/
	public static function init($key = self::EX_ENABLED)
	{
		//init globals
		ExGlobals::init();
		
		//init logger
		//eXLog::init();	
		
		//init security
		//eXSecurity::init();
		//lets init page instance
		
		
		if($key !== FALSE)
		{
			$instance = ExInstance::init(
				($key && $key!==TRUE) ? $key : (ExGlobals::has('eX_global_url_key') ? ExGlobals::get('eX_global_url_key') : NULL)
			);
				/*$instance = new ExInstance(
					($key && $key!==TRUE) ? $key : (ExGlobals::has('eX_global_url_key') ? ExGlobals::get('eX_global_url_key') : NULL)
				);*/
				/*$instance = ExInstance::init(
					($key && $key!==TRUE) ? $key : (ExGlobals::has('eX_global_url_key') ? ExGlobals::get('eX_global_url_key') : NULL)
				, 'en');*/
				ExGlobals::set('eX_requested_instance', $instance);			

			if($instance instanceof eXInstance)
				$instance->output();
			else
				if(ExGlobals::has('eX_global_url_key') == FALSE)
					throw new eXExceptionUrlKeyNotDefined('Url-key is not defined!');	
				else
					throw new eXExceptionPageNotFound('Url-key not found!');
			
		}
		
		return TRUE;
	}
	
	/**
	* framework autoloader
	*/
	public static function autoload($class_name)
	{
		//include once
		$class_exists = FALSE;
		
		// iterate through the class directories until a match is found
		foreach (self::$class_dirs as $class_dir)
			if (($class_exists = self::load_class($class_dir . $class_name)) == TRUE)
				break;
		return $class_exists;
	}
	
	public static function load_class($class)
	{
		//print $class . '<br>';
		if (file_exists($class . '.inc.php'))
		{				
			require_once $class . '.inc.php';
			return TRUE;
		}
		else
			return FALSE;			
	}

	
}

/**
* spl_autoload_register — Register given function as __autoload() implementation
*/
spl_autoload_register(array('eX','autoload'));