<?php

class ExInstanceSetter
{
	private $instance;
		
	public function __construct($instance)
	{		
		$this->instance = $instance;
	}
	
	final public function create($type_uuid)
	{	
		//check if its not a real init instance
		if($instance_id = $this->instance->get_property('instance_id'))	
			if(intval($instance_id) > 0)
				return $this->instance;		
					
			//lets init type instance and return proper object type			
		$type = ExInstance::init($type_uuid);
		
		if($type instanceof ExInstance)
		{
		
			$this->instance	->set_property('runtime.create', true)
											->set_property('instance_name', 'New ' . $type->get_instance_name())
											->set_property('instance_uuid', ExUuid::create_uuid())
											->set_property('instance_type_id', $type_uuid)
											->set_trace('Set instance to be created')
			;		
		
			$controller = 'ExInstance' . ExString::camelize($type->get_instance_name());
			if(class_exists($controller))
			{
				$data = $this->instance->get_data();
				$this->instance = new $controller();	
				$this->instance->set_data($data, false); //pass false not to overwrite runtime
			}		
			//lets cache it so we can reuse same object in future
			//ExInstance::set_instance_cache($instance_uuid, $this->instance);
		}
		//$this->instance->dump();die;
		return $this->instance;				
	}	
	
	final public function edit()
	{
		$this->instance	->set_property('runtime.edit', true)
										->load_from_db() //automatically load from the database on edit
										->set_trace('Set instance to be editable')
		;		
		
		return $this->instance;
	}
	
	final public function write($data = array())
	{			
		$inst = $this->instance;

		if($inst->get_runtime('create') !== true && $inst->get_runtime('edit') !== true)
			return $inst->set_trace('Instance is not editable!');						
			
		
		//lets check if we have to first create a new instance
		if($inst->get_property('runtime.create') === true)
		{
			if(!($name = $inst->get_property('instance_name')))
			{
				//error
				return $inst;
			}
			if(!($type_uuid = $inst->get_property('instance_type_id')))
			{
				//error
				return $inst;
			}

			//lets create a new instance and return Primary Key
			$pk = eXQueryInstance::create(
				$name, 
				$type_uuid,				
				$inst->get_property('instance_parent_id'),
				$inst->get_property('instance_url_key'),
				$inst->get_property('instance_uuid')
			);			
			
			//if successfully created, save current RUNTIME in var and reload content of the new instance
			if(intval($pk) > 0)
			{
				$inst->unset_property('runtime.create');
				$inst->set_instance_id(intval($pk));
				$inst->set_trace('New instance successfully created. PK: ' . intval($pk));											
					
			}
			else
				return new ExNull;	
		}

		//check for error
		if(!$inst->get_property('instance_id'))
		{
			$inst->set_trace('Error occured! instance_id is not defined.');			
			//error
			return $inst;
		}
				
				
		//if data is passed lets handle it automatically
		if($data && is_array($data))
			foreach($data as $key => $val)
				$inst->set_property($key, $val);
			
		//lets handle auto-uploads if any		
		if(is_array($uploads = $inst->get_runtime('auto_uploads')))	
			foreach($uploads as $property => $file)
				$this->upload_file($property, $file);
		$inst->unset_property('runtime.auto_uploads');
		
		//lets unset runtime not to write it
		$inst->unset_property('runtime');
		
		//convert data to json string		
		$jsonstr = ExJson::encode($inst->get_data());	
		
		//lets continue saving
		//call on_before_write method
		//$inst->on_write();	
		eXQueryInstance::update($inst->get_instance_uuid(), $inst->get_data(), $jsonstr);
//var_dump($jsonstr);
		//lets modify tree only in case if it changed
		if(intval($inst->get_runtime('instance_parent_id')) !== intval($inst->get_instance_parent_id()))
			eXQueryInstance::tree($inst->get_instance_id());		

		//create a history record
		eXQueryInstance::create_history($inst->get_instance_uuid(), 1001, $jsonstr);
		
		//eXQueryInstance::assign($this->get_instance_uuid(), $assigned);
		//$inst->on_write_complete();	
		
		//lets clear cache
		$this->clear_cache();
		
		return $inst;		
	}	

	//
	final public function move($uuid)
	{
		return eXQueryInstance::move($this->get_instance_uuid(), $uuid);		
	}
	
	//
	final public function copy()
	{
		return eXQueryInstance::copy($this->get_instance_uuid());		
	}
	
	//
	final private function upload_file($property, $file)
	{		
		if($file && isset($file['tmp_name']) && intval($file['size']) > 0)
		{
			$real_file_name = basename($file['name']);
			$ext = ExFile::get_file_ext($real_file_name);
			$file_name = ExUuid::create_uuid() . '.' . $ext;
			$path = ExPath::storage($this->instance->get_instance_uuid());
			$destination = $path . $file_name;		
			ExFile::create_dir($path);				
			if(move_uploaded_file($file['tmp_name'], $destination))	
			{
				return $this->instance->set_property($property, array(
					'tmp_name'	=> $file_name,
					'name'			=> $real_file_name,
					'type'			=> $file['type'],
					'size'			=> filesize($destination),
					'ext'				=> $ext
				));
			}						
		}		
	}
	
	
	final public function clear_cache()
	{	
		//$cache_file = ExPath::cache($this->instance->get_instance_uuid());
		$uuid = $this->instance->get_instance_uuid();
		$cache_file = EX_ABS_PATH . $this->instance->get_site_name() . '/cache/' . substr($uuid, 0, 1) . '/' . $uuid . '.' . EX_PHP_FILE_EXT;
		if(file_exists($cache_file))
		{
			$this->instance->set_trace('Deleting cache file: ' . $cache_file);			
			ExFile::delete_file($cache_file);		
		}
	}
		
	// 
	public function __call($method, $args)
	{
		$prefixated = ExString::prefixate($method);		
		if(isset($prefixated['prefix']) && isset($prefixated['sufix'])) 
		{			
			switch($prefixated['prefix'])
			{
				case 'set' :					
					//lets check if property does not contain . means it is a main propety, now lets check if its set to edit=true
					//if not we will set custom dynamic properties in runtime		
					if(strpos($prefixated['sufix'], '.') === false && $this->get_runtime('edit') !== true)
						return $this->instance->set_property('runtime.properties.' . $prefixated['sufix'], $args[0]);
					else
						return $this->instance->set_property($prefixated['sufix'], $args[0]);
				break;
				
				//lets automatically upload the document, set to auto uplod when data is saved ONLY!
				case 'upload' :
					if(isset($args[0]['tmp_name']) && intval($args[0]['size']) > 0)
						return $this->instance->set_runtime('auto_uploads.' . $prefixated['sufix'], $args[0]);
					return $this->instance;
				break;						
				
				case 'unset' :
					return $this->instance->unset_property($prefixated['sufix']);
				break;				
				default:
				return $this->instance;						
			}
		}
		
	}

}


