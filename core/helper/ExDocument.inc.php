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

class ExDocument
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
	public static function force($file, $file_name)	
	{
		self::download($file, $file_name, 'attachment');
	}
	
	public static function inline($file, $file_name)	
	{
		self::download($file, $file_name, 'inline');
	}
	
	
	public static function download($file, $file_name, $disposition = 'attachment', $mime = 'application/octet-stream')	
	{
		header('Content-Type: ' . $mime);
		header('Content-Disposition: ' . $disposition . '; filename="' . $file_name . '"');
		header('Content-Transfer-Encoding: binary');
		header('Cache-Control: must-revalidate, pre-check=0, post-check=0');
		header('Pragma: public');
		header('Expires: 0');
		header('Content-Length: ' . filesize($file));
		
		ob_clean();
    flush();
		readfile($file);
		exit;
	}
		
  
	
}