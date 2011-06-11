<?php

/**
	* Truncates text.
	*
	* Cuts a string to the length of $length and replaces the last characters
	* with the ending if the text is longer than length.
	*
	* @param string  $text String to truncate.
	* @param integer $length Length of returned string, including ellipsis.
	* @param string  $ending Ending to be appended to the trimmed string.
	* @param boolean $exact If false, $text will not be cut mid-word
	* @param boolean $considerHtml If true, HTML tags would be handled correctly
	* @return string Trimmed string.
	*/

class ExPath
{
	
	/**
	* Truncates text.
	*
	* Cuts a string to the length of $length and replaces the last characters
	* with the ending if the text is longer than length.
	*
	* @param string  $text String to truncate.
	* @param integer $length Length of returned string, including ellipsis.
	* @param string  $ending Ending to be appended to the trimmed string.
	* @param boolean $exact If false, $text will not be cut mid-word
	* @param boolean $considerHtml If true, HTML tags would be handled correctly
	* @return string Trimmed string.
	*/
	public static function controller($file_name = null)	
	{
		return EX_PATH_CONTROLLER . ($file_name ? ($file_name . '.' . EX_PHP_FILE_EXT) : '');
	}
	
	public static function view($file_name = null)	
	{
		return EX_PATH_VIEW . ($file_name ? ($file_name . '.' . EX_PHP_FILE_EXT) : '');
	}
	
	
	public static function cache($identity)	
	{
		return EX_PATH_CACHE . substr($identity, 0, 1) . '/' . $identity . '.' . EX_PHP_FILE_EXT;
	}
	
	public static function storage($uuid)	
	{
		if($uuid instanceof ExInstance)
			$uuid = $uuid->get_instance_uuid();
		return EX_PATH_STORAGE . substr($uuid, 0, 1) . '/';
	}

	
	
  
	
}