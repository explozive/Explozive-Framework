<?php if(!defined('EX_ABS_PATH')) exit('Access denied!');

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
 * @name ExDocument
 * @version 1.0
 * @package ExploziveFramework
 * @subpackage Helper
 * @access public
 * @author CDC <cdc@explozive.com>
 * @link http://www.explozive.com/api/documentation/article/
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