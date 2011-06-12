<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

defined('EX_SITE_FOLDER') or define('EX_SITE_FOLDER', 'explozive.com');

defined('EX_REL_ROOT') or define('EX_REL_ROOT', '');
defined('EX_REL_PATH') or define('EX_REL_PATH', 'explozive/');
defined('EX_ABS_PATH') or define('EX_ABS_PATH', dirname(__FILE__) . '/');

header("Content-Type: text/html; charset=UTF-8");

// include framework
require_once(dirname(__FILE__) . '/core/Ex.inc.php');


//initialize framework, pass url key or uuid, otherwise it will be taken by default
eX::init();

//dump logger, if u wish to disable logger please comment the line below
//eXLog::dump();

