<?php

class ExInstanceView extends ExInstance
{			
	
	public static function init($identity = null, $language = 'en')
	{			
		return ExInstance::init(array(
			'identity'	=>  $identity,
			'type'			=>  ExInstance::INSTANCE_TYPE_VIEW,
			'path'			=>	ExPath::view($identity),
			'source'		=>	ExInstance::INSTANCE_SOURCE_FILE
		),$language);
		
	}
	
	
	public function __construct()
	{
		parent::__construct();	
	}	
	
	public function run()//$properties = array()
	{		
		foreach($this->get_properties() as $key => $val)
			$$key = $val;
		
		//lets extract custom runtime properties
		foreach($this->get_runtime('properties') as $key => $val)
			$$key = $val;
			
		if($this->get_runtime('source') == 'file' && file_exists($file = $this->get_runtime('path')))
		{		
			if ((bool) @ini_get('short_open_tag') === false)
				echo eval('?>'.preg_replace("/;*\s*\?>/", "; ?>", str_replace('<?=', '<?php echo ', file_get_contents($file))));
			else
				include($file); 
		}
		
		//extract($properties);
		
		return;
		
		//check if this is a PHP view 
		if($this->instance->get_php_view() == 1)
		{		
			$file = $this->instance->get_abs_path();
			
			if (is_file($file) == TRUE) 
			{
				require_once($file);
				$class_name = 'eXModel_' . $this->instance->get_id();
				if(class_exists($class_name))
				{
					$model = new $class_name($this->instance);
					if(method_exists($model, 'init'))
						return $model->init($this->instance);
					else
						return "";
				}	
			}
			else
			{
				trigger_error(__METHOD__ . ': Cannot create cache file for : ' . $this->instance->get_id(), E_USER_ERROR);
				return NULL;
			}
		}
		//working with simple HTML view
		else
		{
			return $this->instance->get_html_code();
		}
	}
	
	
	//
	public function on_init()
	{
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
