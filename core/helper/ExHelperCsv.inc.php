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
 * @name ExploziveFramework
 * @version 2.0
 * @copyright	Copyright (c) 2011 Explozive.com
 */
 
 /**
 * This class adds missing and simplifies some methods to help us working with arrays
 *
 * @name ExHelperCsv
 * @version 1.0
 * @package ExploziveFramework
 * @subpackage Helper
 * @access public
 * @author CDC <cdc@explozive.com>
 * @link http://www.explozive.com/api/documentation/article/
 */

class ExHelperCsv
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
	
}