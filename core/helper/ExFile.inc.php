<?php

class ExFile
{

	public static function create_dir($dir, $permissions = 0777)
	{
		$file = self::get_filename($dir);
		if($file)
			$dir = str_replace($file, '', $dir);
			
		if (FALSE == is_dir($dir)) 
			return mkdir($dir, $permissions, TRUE);
		else
			return TRUE;
	}	
	
	public static function get_filename($path)
	{
		if(!$path) return FALSE;
		$path = preg_split("/[\/\\\]/", $path, -1, PREG_SPLIT_NO_EMPTY);
		$path = $path[count($path)-1];
		return (preg_match('/\./i', $path)) ? $path : FALSE;
	}
	
	public static function get_file_name($path)
	{
		if(!$path) return FALSE;
		$path = preg_split("/[\/\\\]/", $path, -1, PREG_SPLIT_NO_EMPTY);
		$path = $path[count($path)-1];
		return (preg_match('/\./i', $path)) ? $path : FALSE;
	}
	
	public static function get_file_name_noext($path)
	{
		$file_name = self::get_file_name($path);
		if(!$file_name) return FALSE;
		$file_name = preg_split("/[\.]/", $file_name, -1, PREG_SPLIT_NO_EMPTY);
		return (isset($file_name[0])) ? $file_name[0] : FALSE;
	}
	
	public static function get_file_ext($path)
	{
		$exts = preg_split("/[\/\\\.]/", $path, -1, PREG_SPLIT_NO_EMPTY);
		if(count($exts) < 1) return false;
		$ext = $exts[count($exts)-1];
		return (preg_match('/[A-Za-z]{1,4}/i', $ext)) ? $ext : FALSE;
	}
	
	public static function read($file)
	{
		if (is_file($file)) 
			return file_get_contents($file);
		else 
			return FALSE;
	}
	
	public static function write($file, $data, $attributes = NULL, $permissions = 0777)
	{
		if(FALSE == self::create_dir($file))
			return FALSE;
    
		$written = file_put_contents($file, $data, $attributes);
			
		if($written)		
			@chmod($file, $permissions);	
			
		return $written;
	}
	
	public static function delete_file($sFilePath)
	{
		if(!is_file($sFilePath)) return FALSE;
		return @unlink($sFilePath);
	}
	
	
	
	
	
	
	
	
		
	public static function copy_file($file, $destination)
	{
		if(!is_file($sFileSource)) return FALSE;
		$_sDestination = preg_split("/[\/\\\]/", $sFileDestination, -1, PREG_SPLIT_NO_EMPTY);
		array_pop($_sDestination);

		if(FALSE == self::assertDirectoryStructure(implode("/", $_sDestination)))
			return FALSE;

		$bStatus = copy($sFileSource, $sFileDestination);

		if($bStatus && intval($iPerms))
			self::setPermissions($sFileDestination, $iPerms);

		return $bStatus;
	}

	//never pass an absolute path to the source dir for security purposes, must be relative
	// client_asset/ ...
	public static function copyDir($sDirSource, $sDirDestination, $iPerms = 0777, $bStatus = FALSE)
	{
		$sDirSource = trim($sDirSource);
		if(preg_match('/^\//', $sDirSource)) return FALSE;

		if(is_dir($sDirDestination)) $bStatus = TRUE;
		else $bStatus = @mkdir($sDirDestination, $iPerms);
		if(!$dh = @opendir($sDirSource)) return FALSE;
		while (($obj = readdir($dh))) {
			if($obj=='.' || $obj=='..') continue;
			$sSource = $sDirSource . '/' . $obj;
			$sDest = $sDirDestination . '/' . $obj;
			if (is_dir($sSource)) $bStatus = self::copyDir($sSource, $sDest, $iPerms, $bStatus);
			else $bStatus = @copy($sSource, $sDest);
		}
		return $bStatus;
	}

	public static function getLastError()
	{
		return self::$sLastError;
	}

	public static function getFileViaHttp($sFileSource, $sFileDestination, $aAttribs = array(), $iPerms = 0777)
	{
		if(FALSE == self::assertDirectoryStructure($sFileDestination))
			return FALSE;

		$fp = fopen($sFileDestination, 'w');
		if(FALSE == $fp)
		{
			trigger_error(fmthd(__METHOD__) . '(): Failed to write into file "' . $sFileDestination . '".', E_USER_ERROR);
			return FALSE;
		}

		$ch = curl_init($sFileSource);
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_FAILONERROR, 1);
		curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
		foreach($aAttribs as $sKey => $sValue)
			curl_setopt($ch, $sKey, $sValue);

		$bStatus = curl_exec($ch);

		//check http code
		$iHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if($bStatus == FALSE && curl_errno($ch) == 52 || $iHttpCode != 200)
		{		//when file does not exists and empty reply from the server
			Logger::Trigger(E_PRISM_NOT_FOUND, fmthd(__METHOD__) . '(): ' . curl_error($ch) . '. Error: ' . curl_errno($ch) . ', HTTP Response: ' . $iHttpCode, __FILE__, __LINE__);
			$bStatus = FALSE;
		}
		else if($bStatus == FALSE)
			trigger_error(fmthd(__METHOD__) . '(): ' . curl_error($ch) . '. Error: ' . curl_errno($ch) . ', HTTP Response: ' . $iHttpCode, E_USER_ERROR);

		fclose($fp);
		curl_close($ch);

		//an error occured
		// [kp 2010-02-04] DISABLED -- is returning int(0) for the new file on PS(CMA) server even when the file is fine!
		// Dima S., to replace this check, instead of returning empty content for the file, set headers on return to
		// header('HTTP/1.1 500 Internal Server Error');
		//if(filesize($sFileDestination) == 0)
			//$bStatus = FALSE;

		if($bStatus && intval($iPerms))
			self::setPermissions($sFileDestination, $iPerms);

		if(!$bStatus && is_file($sFileDestination))
			self::deleteFile($sFileDestination);

		return $bStatus;
	}

	public static function renameFile($sFileSource, $sNewFileName)
	{
		$sNewFileName = preg_split("/[\/\\\]/", $sNewFileName, -1, PREG_SPLIT_NO_EMPTY);
		$sNewFileName = $sNewFileName[count($sNewFileName)-1];
		if(@rename($sFileSource, self::getRelativePathOf($sFileSource) . $sNewFileName)) return TRUE;
		else return FALSE;
	}

	public static function renameDir($sDirSource, $sNewDirName)
	{
		$sNewDirName = preg_split("/[\/\\\]/", $sNewDirName, -1, PREG_SPLIT_NO_EMPTY);
		$sNewDirName = $sNewDirName[count($sNewDirName)-1];
		if(@rename($sDirSource, self::getRelativePathOf($sDirSource) . $sNewDirName)) return TRUE;
		else return FALSE;
	}

	

	public static function deleteDir($sDir, $bRecursive = TRUE, $bDeleteDir = TRUE, $sFilter = NULL)
	{
		$dir = trim($sDir);
		if (!is_dir($dir))
			return FALSE;

		// Cannot delete these directories
		if ($dir=='' || $dir== '/' || $dir == G_DIR_ABS_ROOT
					|| preg_match('/(prism_library[\/]?$)|(prism_service[\/]?$)|(prism_configuration[\/]?$)|(prism_redirect[\/]?$)|(multimedia[\/]?$)|(client_asset[\/]?$)|(client_configuration[\/]?$)/i', $dir)) {
			return FALSE;
		}

		// Get directory entries
		$files = scandir($dir);
		foreach ($files as $obj) {
	        // Skip filtered entries
			if ($obj == '.' || $obj == '..' || ($sFilter && !preg_match($sFilter, $obj)))
				continue;

			// Is directory
			if (is_dir($dir.'/'.$obj)) {
				// Remove directories recursively
				if ($bRecursive)
					self::deleteDir($dir.'/'.$obj, $bRecursive, TRUE, $sFilter);
				continue;
			}

			// Remove file
			@unlink($dir.'/'.$obj);
		}

		// Remove directory
	    if ($bDeleteDir == TRUE)
			return @rmdir($dir);
		else
			return TRUE;
	}

	

	public static function moveFile($sFileSource, $sDestination)
	{
		if((self::getRelativePathOf($sDestination) != '' && FALSE == self::assertDirectoryStructure($sDestination)) ||
				FALSE == is_file($sFileSource))
			return FALSE;

		if(FALSE == self::getFileName($sDestination)) $sDestination .= '/' . self::getFileName($sFileSource);

		if (!@rename($sFileSource, $sDestination))
		{
			if (copy ($sFileSource, $sDestination))
			{
				@unlink($sFileSource);
				return TRUE;
			}
			return FALSE;
   		}
		return TRUE;
	}

	public static function moveDir($sDirSource, $sDestination)
	{
		self::assertDirectoryStructure(self::getRelativePathOf($sDestination));
		if(@rename($sDirSource, $sDestination)) return TRUE;
		else return FALSE;
	}

	public static function uploadFile($sFieldName, $sDestination, $iPerms = 0777)
	{
		if(isset($_FILES[$sFieldName]))
		{
			if(FALSE == self::getFileName($sDestination)) $sDestination .= '/' . $_FILES[$sFieldName]['name'];

			if ($_FILES[$sFieldName]["error"] > 0)	return FALSE;

			$bStatus = self::moveFile($_FILES[$sFieldName]["tmp_name"], $sDestination);
			if($bStatus && intval($iPerms))
				self::setPermissions($sDestination, $iPerms);

			return $sDestination;
		}
		return FALSE;
	}

	

	public static function getFileExt($sFilePath)
	{
		$exts = preg_split("/[\/\\\.]/", $sFilePath, -1, PREG_SPLIT_NO_EMPTY);
		$ext = $exts[count($exts)-1];
		return (preg_match('/[A-Za-z]{1,4}/i', $ext)) ? $ext : FALSE;
	}

	public static function read_dir($sDir, $aSortby = '', $sFilter = NULL, $bPermissions = FALSE)
	{
		if ($sDir[strlen($sDir)-1] != '/') $sDir .= '/';
		if (!is_dir($sDir)) return array();
		$dir_handle  = opendir($sDir);
		$dir_objects = array();
		while ($object = readdir($dir_handle))
			if (!in_array($object, array('.','..')))
			{
				if (!$sFilter || preg_match($sFilter, $object) )
				{
					$filename    = $sDir . $object;
					$file_object = array(
	                    'name' => $object,
	                    'size' => filesize($filename),
	                    'type' => filetype($filename),
											'ext' => self::getFileExt($object),
											'unixtime' => filemtime($filename),
	                    'owner' => ($bPermissions) ? posix_getpwuid(fileowner($filename)) : '',
											'permissions' => ($bPermissions) ? self::getPermissions($filename) : '',
											'time' => ExDate::format("d F Y H:i:s", filemtime($filename))
					);
					$dir_objects[] = $file_object;
				}
			}
		//lets resort array if required
		if(!is_array($aSortby))
			$aSortby =  array('time', 'asc');

		$dir_objects = self::columnSort($dir_objects, $aSortby);

		return $dir_objects;
	}

	public static function getPermissions($sFilePath)
	{
      $perms = fileperms($sFilePath);
			if(FALSE == $perms) return FALSE;
      if     (($perms & 0xC000) == 0xC000) { $info = 's'; }
      elseif (($perms & 0xA000) == 0xA000) { $info = 'l'; }
      elseif (($perms & 0x8000) == 0x8000) { $info = '-'; }
      elseif (($perms & 0x6000) == 0x6000) { $info = 'b'; }
      elseif (($perms & 0x4000) == 0x4000) { $info = 'd'; }
      elseif (($perms & 0x2000) == 0x2000) { $info = 'c'; }
      elseif (($perms & 0x1000) == 0x1000) { $info = 'p'; }
      else                                 { $info = 'u'; }
      $info .= (($perms & 0x0100) ? 'r' : '-');
      $info .= (($perms & 0x0080) ? 'w' : '-');
      $info .= (($perms & 0x0040) ? (($perms & 0x0800) ? 's' : 'x' ) : (($perms & 0x0800) ? 'S' : '-'));
      $info .= (($perms & 0x0020) ? 'r' : '-');
      $info .= (($perms & 0x0010) ? 'w' : '-');
      $info .= (($perms & 0x0008) ? (($perms & 0x0400) ? 's' : 'x' ) : (($perms & 0x0400) ? 'S' : '-'));
      $info .= (($perms & 0x0004) ? 'r' : '-');
      $info .= (($perms & 0x0002) ? 'w' : '-');
      $info .= (($perms & 0x0001) ? (($perms & 0x0200) ? 't' : 'x' ) : (($perms & 0x0200) ? 'T' : '-'));

      return $info;
	}

	public static function setPermissions($sFilePath, $iPerms = 0777)
	{
		if('' != trim($iPerms) && TRUE == preg_match('/\d{3,4}/', $iPerms )) {
			@umask(0);
			@chmod($sFilePath, $iPerms);
		}
		return TRUE;
	}

	/*	For System use only, use createDir instead	*/
	public static function assertDirectoryStructure($sPath, $iPerms = 0777)
	{
		$sFileName = self::getFileName($sPath);
		if($sFileName)
			$sPath = str_replace($sFileName, '', $sPath);
		// directories will be created recursively if they don't exist
		if (FALSE == is_dir($sPath)) {
			if ('' == trim($iPerms) || !preg_match('/\d{3,4}/', $iPerms))
				$iPerms = 0777;
			umask(0);
			return mkdir($sPath, $iPerms, TRUE);
		}
		else
			return TRUE;
	}

	/*	returns any relative path for example passing temp/temp2/temp3 , returns temp/temp2/
																										temp/temp2/text.txt, returns temp/temp2/
	*/
	public static function getRelativePathOf($sPathSource)
	{

		$aPathSource = preg_split("/[\/\\\]/", preg_replace('/(\/)$/', '', $sPathSource), -1, PREG_SPLIT_NO_EMPTY);
		array_pop($aPathSource);
		$sNewPath = implode("/", $aPathSource);
		if(count($aPathSource) > 1) $sNewPath .= '/';
		return $sNewPath;
	}

	/*	HELPFUL METHODS FOR THE readDir functionality, not for public use	*/
	private static function columnSort($recs, $cols)
	{
    global $prismFileGlobalMultisortVar;
    $prismFileGlobalMultisortVar = $cols;
    usort($recs, array('self', 'multiStrnatcmp'));
    return($recs);
	}
	private static function multiStrnatcmp($a, $b)
	{
	    global $prismFileGlobalMultisortVar;
	    $cols = $prismFileGlobalMultisortVar;
	    $i = 0;
	    $result = 0;
	    while ($result == 0 && $i < count($cols)) {
	        $result = ($cols[$i + 1] == 'desc' ? strnatcmp($b[$cols[$i]], $a[$cols[$i]]) : $result = strnatcmp($a[$cols[$i]], $b[$cols[$i]]));
	        $i+=2;
	    }
	    return $result;
	}

}