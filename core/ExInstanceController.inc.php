<?php

class ExInstanceController extends ExInstance
{			
	
	public static function init($identity = null, $language = 'en')
	{			
		return ExInstance::init(array(
			'identity'	=>  $identity,
			'type'			=>  ExInstance::INSTANCE_TYPE_CONTROLLER,
			'path'			=>	ExPath::view($identity),
			'source'		=>	ExInstance::INSTANCE_SOURCE_FILE
		),$language);
	}
	
	
	public function __construct()
	{
		parent::__construct();
	}
	
	//
	public function on_init()
	{
		var_dump($this->get_abs_path());
		
		if(file_exists($this->get_abs_path()) == FALSE)
			$this->write_file();			
	}	
	
	//
	public function on_write_complete()
	{
		$this->write_file();		
	}	
	
	//
	public function get_abs_path()
	{
		return sprintf('%s%s.inc.php', EX_CACHE_ABS_PATH, $this->get_instance_uuid());
	}	
	
	//
	public function get_file_content()
	{
		return $this->get_php_code();
	}		
	
	//
	private function write_file()
	{
		$file = $this->get_abs_path();
		
		$phpcode = preg_replace('/class[\s]+eXModel[\s]{1}/', 'class eXModel_' . $this->get_id() . ' ', $this->get_php_code(), 1);
		ExFile::write($file, $phpcode);		
	}	
	
}
