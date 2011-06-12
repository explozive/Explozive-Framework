<?php

/***********************************************************************
*	Note: If the number of the year is specified in a two digit format, 
*	the values between 00-69 are mapped to 2000-2069 and 70-99 to 1970-1999. 
************************************************************************/
define('G_STR_DEFAULT_TIMEZONE', 'America/New_York');
class ExDate
{
	const DB_FORMAT 					= 'Y-m-d H:i:s';
	const GUI_DATE_FORMAT 		= 'm/d/Y';
	const GUI_TIME_FORMAT 		= 'H:i:s';
	const PHP_START_ERA_YEAR 	= 1902;
	const PHP_END_ERA_YEAR 		= 2037;
		
	
	//
	public static function microtime_float() 
	{
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}
	
	
	public static function is_date($str) 
	{
		$in = preg_replace('/:\d{3}/', ' ', $str);
		return (boolean) strtotime($str);
	}
		
	
	// parse any date/time string and covert to a database format
	public static function formatToDb($sDateTime = NULL)
	{
		if(!$sDateTime) $sDateTime = time();
		return self::_format($sDateTime, self::DB_FORMAT);
	}
	
	// 
	public static function format($sFormatString = self::DB_FORMAT, $sDateTime = NULL)
	{
		if(!$sDateTime) $sDateTime = time();
		return self::_format($sDateTime, $sFormatString);
	}
	
	/* current method compares dates and returns
			-1, if date1 is less than date2
			0, 	if date1 is equal to date2
			1, 	if date1 is greater than date2
	*/
	public static function compare($sDateTime, $sDateTime2)
	{		
		//fix year to be between 1900 - 2038		
		$oDateTime = self::_getobject($sDateTime);
		$oDateTime2 = self::_getobject($sDateTime2);		
		
		$iDateTimeYear = intval($oDateTime->format('Y'));
		if($iDateTimeYear < self::PHP_START_ERA_YEAR)	$oDateTime->modify('+' . intval(self::PHP_START_ERA_YEAR-$iDateTimeYear) . ' year');
		else if($iDateTimeYear > self::PHP_END_ERA_YEAR)
			$oDateTime->modify('-' . intval($iDateTimeYear - self::PHP_END_ERA_YEAR) . ' year');
		
		$iDateTimeYear2 = intval($oDateTime2->format('Y'));
		if($iDateTimeYear2 < self::PHP_START_ERA_YEAR)	$oDateTime2->modify('+' . intval(self::PHP_START_ERA_YEAR-$iDateTimeYear2) . ' year');
		else if($iDateTimeYear2 > self::PHP_END_ERA_YEAR)
			$oDateTime2->modify('-' . intval($iDateTimeYear2 - self::PHP_END_ERA_YEAR) . ' year');
				
		$iDateTime = strtotime($oDateTime->format(self::DB_FORMAT));			
		$iDateTime2 = strtotime($oDateTime2->format(self::DB_FORMAT));		
		if ($iDateTime < $iDateTime2) return -1;
		else if ($iDateTime > $iDateTime2) return 1;
		else return 0;
	}
	
	//
	public static function formatToGuiDate($sDateTime = NULL)
	{	
		if(!$sDateTime) $sDateTime = time();
		return self::_format($sDateTime, self::GUI_DATE_FORMAT);
	}
	
	//get month name
	public static function getMonthName($m = 0) {
		return (($m==0 ) ? date("F") : date("F", mktime(0,0,0,$m)));
	}
	
	//get number of days in month
	public static function getDaysInMonth($m = 0) {
		$aDays = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
		return intval($aDays[$m]);
	}
	
	//
	public static function formatToGuiTime($sDateTime = NULL)
	{	
		if(!$sDateTime) $sDateTime = time();
		return self::_format($sDateTime, self::GUI_TIME_FORMAT);
	}
	
	//
	public static function formatToGuiDateTime($sDateTime = NULL)
	{	
		if(!$sDateTime) $sDateTime = time();
		return self::_format($sDateTime, self::GUI_DATE_FORMAT . ' ' . self::GUI_TIME_FORMAT);
	}
	
	//
	private static function _format($sDateTime, $sFormatString)
	{
		if ('' == trim($sDateTime)) {
			return '';
		}
		else {
			date_default_timezone_set(G_STR_DEFAULT_TIMEZONE);
			//convert unix time into date first
			if (is_numeric($sDateTime))
				$sDateTime = date(self::DB_FORMAT, $sDateTime);
			//handle PRISM_SYSTEM_DATETIME and PRISM_DB_DATETIME, use current time
			elseif (trim($sDateTime) == 'PRISM_SYSTEM_DATETIME' || trim($sDateTime) == 'PRISM_DB_DATETIME')
				$sDateTime = date(self::DB_FORMAT);
			
			//MSSQL data format fix
			$sDateTime = preg_replace('/:\d{3}/', ' ', $sDateTime);
			
			// Format the date
			$oDateTime = self::_getobject($sDateTime);
			return $oDateTime->format($sFormatString);		
		}
	}
	
	//
	private static function _getobject($sDateTime)
	{
		//convert unix time into date first
		if(is_numeric($sDateTime)) $sDateTime = date(self::DB_FORMAT, $sDateTime);
		//MSSQL date format fix
		$sDateTime = preg_replace('/:\d{3}/', ' ', $sDateTime);
		//ORACLE date format fix
		if(TRUE == preg_match('/{ts[\s]*\'([\s\S]*)\'}/i', $sDateTime, $aRegs))
			$sDateTime = $aRegs[1];
		//ORACLE date format fix DD-MMM-YY
		if(TRUE == preg_match('/^(\d{1,2}-[A-Z]{3}-)(\d{1,2})$/i', $sDateTime, $aRegs))
			$sDateTime = $aRegs[1] . '20' . $aRegs[2];
		//set default timezone as required from PHP 5.3
		date_default_timezone_set(G_STR_DEFAULT_TIMEZONE);
		return new DateTime($sDateTime);
	}
	
	
}

