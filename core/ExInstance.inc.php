<?php

class ExInstance
{
	private static $conn = NULL;
	private static $instances = array();
	public static $output;
	protected $xml;
	private $instance_data = array();
	private $type_name;
	private $model;
	private $page;
	private $controller;	
	private $properties = array();	
	private $setter;	
	
	
	const INSTANCE_SOURCE_FILE 			= 'file';
	const INSTANCE_SOURCE_DB 				= 'db';
	const INSTANCE_SOURCE_CACHE 		= 'cache';
	
	const INSTANCE_TYPE_CONTROLLER	= 'controller';
	const INSTANCE_TYPE_VIEW				= 'view';
	

	//private $instance = array();
	
	const ASSIGN_LABEL 					= 'eX-label';
	const ASSIGN_STYLESHEET 		= 'eX-stylesheet';
	const ASSIGN_VIEW 					= 'eX-view';
	const ASSIGN_JAVASCRIPT 		= 'eX-javascript';
	
	const GET_VALUE_AS_ARRAY		= 0;
	const GET_VALUE_AS_STRING		= 1;
	
	const UNIQUE_TYPE							= 0;
	const UNIQUE_TYPE_UUID				= 1;
	const UNIQUE_TYPE_VALUE				= 2;
	const UNIQUE_TYPE_UUID_VALUE	= 3;
	
	const XML_ROOT_VERSION = 'version_descriptor';
	const XML_INI_PROPETIES = 'eX_ini_properties';
	
	//list of assignments that is allowed multiple assignment
	private $unqiue_type_value = array(
		
		self::ASSIGN_VIEW,
	);
	//list of assignments that is allowed multiple assignment
	private $unqiue_type_uuid_value = array(
		self::ASSIGN_STYLESHEET,
		self::ASSIGN_LABEL,
		self::ASSIGN_JAVASCRIPT,  
	);
	
	//lets init a new Instance object
	//$identity is NULL if we are creating a new Instance
	public static function init($identity = null, $language = 'en')
	{	
		//if $identity is passed as an array of key => value lets extract and set the runtime data
		if(is_array($identity))
			extract($identity);
		
		//lets consider that by default we are calling controller
		//lets first check if conroller exists for this site		
		if($identity && is_string($identity))		
		{
			$class_name = ExString::camelize($identity);
			
			//lets assume that by default we have controller always			
			$file = (isset($path)) ? $path : ExPath::controller($class_name);//by default always check for controller
			
			//first lets check if this is a name of the file passed and init static-file controller			
			if(file_exists($file))
			{
				//lets init only if its controller
				if(isset($type) && $type === self::INSTANCE_TYPE_VIEW)		
					$identity = 'ExInstance' . $type;	
				else
					include_once($file);	
										
							
				if(class_exists($class_name = ExString::camelize($identity)))
				{					
					$instance = new $class_name();
					$instance	->set_runtime('loaded', true)
										->set_runtime('identity', $identity)
										->set_runtime('language', $language)
										->set_runtime('source', self::INSTANCE_SOURCE_FILE)
										->set_runtime('path', $file)
										->set_runtime('type', isset($type) ? $type : self::INSTANCE_TYPE_CONTROLLER)
										->set_trace('Class ' . $class_name . ' exists. Loading...')
										;
							
					return $instance;					
				}
			}
			else//if identity is not a static file
			{
				//lets check cache
				if(($instance = self::get_instance_cache($identity)) instanceof ExInstance && 1 == 0)
					return $instance;	
				else
				{					
					$instance = new ExInstance($identity);	
					//try to load from cache
					$instance->load_from_cache();
		  		if($instance->is_loaded() !== true)
					{
						//failed to load from cache, try db
						$instance->load_from_db();		
					}
					
					
					
					//instance successfully loaded
					if($instance->is_loaded() === true)
					{
						$data = $instance->get_data();
						$controller = 'ExInstance' . ExString::camelize($instance->get_instance_type_name());
						if(class_exists($controller))
						{
							$instance = new $controller();	
							$instance->set_data($data, false); //pass false not to overwrite runtime
						}
						//set parent id so we can check if it was changed on save to regenerate tree
						$instance->set_runtime('instance_parent_id', isset($data['instance_parent_id']) ? $data['instance_parent_id'] : null);
						return $instance;			
					}
					else
						return new ExNull();									
				}
			}
		}
		else //identity is not provided return null object
		{
			return new ExNull();
		}
		
		//lets cache it in a memory to be used within the request
		//$this->set_instance_cache($this->get_instance_uuid(), $this);		
	}
	
	
	public static function init_file($identity = null, $language = 'en')
	{		
	
	
	}
	
	public static function init_instance($identity = null, $language = 'en')
	{		
	
	
	}
	
	
	public function __construct($identity = NULL, $language = 'en')
	{		
		//lets init output class
		if(self::$output instanceof ExOutput == false)
			self::$output = new ExOutput($this);
		
		//if $identity is passed as an array of key => value lets extract and set the runtime data
		if(is_array($identity))
		{
			extract($identity);
		}
		
		/*
			$this->instance_data -  is a main json object
				identity - identified user to init, key-url, uuid or file name
				source - loaded from, db, cache or file
				language - requested language, en, fr, ru and etc
				loaded - status of the object data		
		*/		
		$this->instance_data = array(
			'runtime'	=> array(
				'identity'			=> $identity,
				'source'				=> (isset($source) ? $source : 'uknown'),
				'path'					=> (isset($path) ? $path : null),
				'language'			=> $language,
				'loaded'				=> (isset($loaded) ? $loaded : false),
				'site'					=> null,
				'caller'				=> 'html',
				'properties'		=> array(), //dynamic properties array
				'trace'					=> array()
			),
			'instance_uuid'		=> $identity,
			'instance_parents' => array(
				//default is nothing until we load data
			),						
			//assigned instances to this instance
			'instance_assignments' => array(
				//default is nothing until we load data
			)						
		);			
	}
	
	public static function set_instance_cache($identity, ExInstance $instance)
	{
		if($identity) {
			self::$instances[$identity] = $instance;
			return true;
		}
		return false;
	}
	
	public static function get_instance_cache($identity)
	{
		return (isset(self::$instances[$identity])) ? self::$instances[$identity] : null;
	}
	
		
	
	final public function get_data()
	{	
		return $this->instance_data;
	}
	
	final public function set_data($data, $runtime_overwrite = false)
	{			
		//lets copy runtime
		if($runtime_overwrite)
			$data['runtime'] = isset($this->instance_data['runtime']) ? $this->instance_data['runtime'] : array();
		$this->instance_data = is_array($data) ? $data :	ExJson::decode($data);
		return $this;
	}
	
	
	/*
		loads data into the instance from cache
	*/
	final public function load_from_cache()
	{	
		$cache_file = ExPath::cache($this->get_instance_uuid());		
		
		//set runtime properties
		$this	->set_runtime('loaded', false)
					->set_runtime('path', $cache_file)
					->set_runtime('source', self::INSTANCE_SOURCE_CACHE)
					;
		
		//lets check if cache file exists load it
		if(file_exists($cache_file))
		{
			$instance_data = ExJson::decode(ExFile::read($cache_file));		
			
			//unset runtime block just incase
			if(isset($instance_data['runtime']))	
				unset($instance_data['runtime']);			
			
			$this	->set_data($instance_data, true) //pass true to overwrite runttime
						->set_runtime('loaded', true)
						->set_trace('Successfully loaded from cache')
						;
		}
		else //failed to load from cache
		{
			$this	->set_runtime('loaded', false)
						->set_runtime('path', $cache_file)
						->set_trace('Failed to load from cache file. File does not exist')
						;
		}	
		return $this;
	}
	
	
	/*
		loads data into the instance from db
	*/
	final public function load_from_db()
	{	
		$instance_data = array();
		$data = eXQueryInstance::load($this->get_instance_uuid());				
		
		//set runtime properties
		$this	->set_runtime('loaded', false)
					->set_runtime('source', self::INSTANCE_SOURCE_DB)
					;
		
		if($data) //if record is found
		{
			//lets reparse content properties
			if(isset($data['instance_content']) && ($instance_content = $data['instance_content']) !== null)
			{
				unset($data['instance_content']);
				$instance_data = ExJson::decode(utf8_decode($instance_content));
				//unset runtime block just incase
				if(isset($instance_data['runtime']))	
					unset($instance_data['runtime']);			
			}
			
			//lets loop the db column properties to overwrite whatever we have in json
			foreach($data as $key => $value)
				$instance_data[$key] = $value;
				
			//preload assignments	
			$instance_data['instance_assignments'] = eXQueryInstance::load_assignments($instance_data['instance_uuid']);
			
			//load parents
			$instance_data['instance_parents'] = eXQueryInstance::get_parents($instance_data['instance_id']);
			
			//set main object with data
			$this	->set_data($instance_data, true) //pass true to overwrite runttime
						->set_runtime('loaded', true)
						->set_trace('Successfully loaded from db')
						;
		}	
		else //failed to load from db
		{
			$this->set_trace('Failed to load from database');
		}	
		
		return $this;
	}
	
	/*
		loads data into the instance from json string
	*/
	final public function load_from_string($json)
	{	
		//unset runtime block just incase
		//if(isset($instance_data['runtime']))	
			//unset($instance_data['runtime']);			
		$this->set_data($json);
	}
	
	
	final public function dump($die = FALSE)
	{
		eXUtils::dump($this->instance_data, $die);
		return $this;
	}
	
	final public function set_view($view, $properties = array())
	{
		$this->assign('eX-view', $view);		
		//lets predefine runtime custom properties
		foreach($properties as $property => $value)
			$this->set_runtime('properties.' . $property, $value);
		return $this;
	}
	
	final public function set_css($css)
	{
		//$this->assign('eX-stylesheet', $view);
		//$this->set_properties($properties);
		return $this;
	}
	
	//site name and site folder name are the same lets analyze parents and get site name
	final public function get_site_name()
	{
		foreach($this->get_instance_parents() as $parent)
			if($parent['instance_type_uuid'] == EX_UUID_TYPE_SITE)
				return $parent['instance_name'];

		return null;
	}
	
	final public function set_html_header($str)
	{
		return $this->page->set_html_header($str);
	}
	
	
	
	//
	final public function process($caller = NULL, $properties = array())
	{		
		//lets prevent an infinite loop in case of the error
		if(($itter = intval(ExGlobals::get('eX_controller_html_count'))) > 250)
		{
			print "Fatal Error: Recursive call abort (Too many iterations of instance '" . $this->get_instance_name() . "')."; 
			die;
		}
		else
			ExGlobals::set('eX_controller_html_count', $itter+1);		
	
		$method = (ExGlobals::get_requested_instance() == $this) ? ExGlobals::get_method() : 'run';
		//if method does not exists, consider
		if($method !== 'run' && method_exists($this, $method) == false)
		{
			$method = 'run';
		}
		
		if(method_exists($this, $method))
		{
			ob_start();			
			call_user_func_array(array($this, $method), ExGlobals::get_cgi_parameters());
			//$this->run($properties);
			$content = ob_get_contents();
			@ob_end_clean();
		} 
		else
			$content = '';
		
		//lets read preassigned view
		$views = $this->get_assigned("type=='eX-view' and value==null");//
		
		if($views && count($views) > 0)
		{	
			$view = ExInstanceView::init($views[0]['instance_uuid']);						
			//lets pass-on runtime properties of the controller
			$view->set_runtime('properties', $this->get_runtime('properties'));
			//$view->dump();
			//$view->set_properties($this);
			//$view->set_reference($this);		
			$content = preg_replace('/<!--\s*eX-insert-instance\s*-->|\{\s*insert-instance\s*\}/i', $content, $view->process(), 1);
			//preg_match('/<!--\s*eX-insert-instance\s*-->/i', $view, $spots);			
			//if($spots && isset($spots[0]))
				//$content = str_replace($spots[0], $content, $view);			
		}	
		
		
		//lets parse content custom properties		
		$content = ExParser::properties($this, $content);	
		
		
		$instances = ExParser::instances($this, $content);
		if($instances && count($instances) > 0)
			foreach($instances as $props)
			{				
				//if(ExUuid::is_uuid($key))
				//{
					$inject = ExInstance::init($props['value']);	
					if($inject instanceof ExInstance)
					{					
						//$new->set_properties($instance);	
						//$new->set_reference($instance);		
						ob_start();
						print $inject->process();					
						$content = str_replace($props['property'], ob_get_contents(), $content);		
						@ob_end_clean();
	
					}						
				//}	
			}
			
		
		return $content;		
	}
	
	//

	final public function output()
	{
		if(class_exists($class_name = 'ExOutput' . ExString::camelize($this->get_output_type())) == false)
			$class_name = "ExOutputHtml";
		
		self::$output = new $class_name($this);
		self::$output->start();
		self::$output->end();		

	}
	
		
	
	
	
		
	
	final public function assign($type, $instance_uuid, $value = NULL, $allow = self::UNIQUE_TYPE_UUID_VALUE)
	{
		if(trim($type) == "") 
			return FALSE;
		
		if(!is_array($instance_uuid)) 
			$instance_uuid = array($instance_uuid);
		
		foreach($instance_uuid as $uuid)
		{
			//let's check if value is passed
			if($value == NULL)
			{
				if(count($arr = explode(':', $uuid)) == 2)
				{
					$uuid = $arr[0];
					$val = $arr[1];
				} else 
					$val = NULL;
			} else
				$val = NULL;
			
			array_push(
				$this->instance_data['instance_assignments'], 
				array(
					'type'	=>	$type,
					'instance_uuid'	=>	$uuid,
					'value'	=>	$val,
				)
			);

		}

		return $this;
		
			
		
		$root = $this->xml->getRootNode();		
		foreach($instance_uuid as $uuid)
		{
			
			

			//lets create a condition to remove nodes
			$xpath = "";
			switch($allow)
			{				
				case self::UNIQUE_TYPE_UUID_VALUE :	//unique index[type, uuid, value]
					$xpath = '@type=\'' . $type . '\' and @instance_uuid=\'' . $uuid . '\' and (not(text()) or text()=\'' . $val . '\')';
				break;
				case self::UNIQUE_TYPE_VALUE :	//unique index[type, value]
					$xpath = '@type=\'' . $type . '\' and text()=\'' . $val . '\'';
				break;
				case self::UNIQUE_TYPE_UUID :	//unique index[type, uuid]
					$xpath = '@type=\'' . $type . '\' and @instance_uuid=\'' . $uuid . '\'';
				break;
				case self::UNIQUE_TYPE :		//unique index[type]
					$xpath = '@type=\'' . $type . '\'';
				break;					
			}
		
			//var_dump($type, $xpath);
			if(trim($xpath) != "")
				foreach($root->getNodes('./eX_assigned['.$xpath.']') as $node)
					if($node instanceof eXmlElement)						
						$node->removeNode();
			
			//add a new node
			//if(ExUuid::is_uuid($uuid))
				$root->createNode('eX_assigned', $val)
						 ->lastChild()
						 ->createAttribute('type', $type)
						 ->createAttribute('instance_uuid', $uuid)
				;
					
		}
		
		//$this->xml->dump();
		
		return $this;	
	}
	
	final public function get_assigned($regex)
	{		
		if(isset($this->instance_data['instance_assignments']))
			return ExArray::array_search($this->instance_data['instance_assignments'], $regex);	
		return array();
	}
	
	final public function unassign($type, $instance_uuid = NULL)
	{		
		if($instance_uuid) 
			$xpath = './eX_assigned[@type=\'' . $type . '\' and @instance_uuid=\'' . $instance_uuid . '\']';
		else
			$xpath = './eX_assigned[@type=\'' . $type . '\']';
		
		foreach($this->xml->getRootNode()->getNodes($xpath) as $node)
			if($node instanceof eXmlElement)
				$node->removeNode();	
		
		return $this;	
	}
	
	/*
	public function get_relative_url()
	{	
		return $_SERVER['PHP_SELF'] . '/';
	}
	
	public function get_instance_url()
	{	
		return EX_PHP_URL . $this->get_instance_uuid();
	}
	*/

	
	
	
	
	//
	final public function get_reference()
	{		
		return isset($this->reference) ? $this->reference : NULL;
	}
	
	//
	final public function set_reference($instance)
	{		
		$this->reference = $instance;
	}
	
	//
	final public function set_properties($properties)
	{
		if($properties instanceof eXInstance)
			$properties = $properties->properties;
		$this->properties = array_merge($this->properties, $properties);
	}
	
	//
	final public function get_properties()
	{
		return $this->get_data();
	}
	
	
	//
	final public function is_loaded()
	{
		return $this->get_runtime('loaded');
	}
	
	
	
	//
	final public function set_property($property_name, $property_value)
	{		
		$node =& $this->instance_data;		
		foreach(explode('.', $property_name) as $property)
			$node = &$node[$property];
		
		$node = $property_value;
		return $this;
	}	
	
	//
	final public function get_property($property_name)
	{
		$node = &$this->instance_data;		
		foreach(explode('.', $property_name) as $property)
		{
			if(isset($node[$property]))
				$node = &$node[$property];
			else
				return null;
		}
		return $node;				
	}
	
	//
	final public function has_property($property_name)
	{
		return ($this->get_property($property_name) === null) ? false : true;
	}
	
	final public function get_runtime($property_name)
	{
		return $this->get_property('runtime.' . $property_name);
	}
	
	final public function set_runtime($property_name, $property_value)
	{
		$this->set_property('runtime.' . $property_name, $property_value);
		return $this;
	}
	
	final public function get_output_type()
	{
		return ExString::coalesce($this->get_property('runtime.output'), 'html');
	}
	
	final public function set_output_type($type)
	{
		$this->set_property('runtime.output', $type);
		return $this;
	}
	
	//
	final public function unset_property($property_name)
	{
		$node = &$this->instance_data;
		$nodes = explode('.', $property_name);
		for($i = 0; $i < count($nodes); $i++)
		{
			if(isset($node[$nodes[$i]]))
			{
				if($i == count($nodes)-1)
					unset($node[$nodes[$i]]);
				else
					$node = &$node[$nodes[$i]];
			}
		}
		return $this;
	}
	
	//
	final public function get_params()
	{
		return $this->params;
	}
	
	//can be TRUE/FALSE 1/0 DATE/NULL
	/*public function set_instance_published($status)
	{
		if($status == TRUE) 
		{
			if( $this->is_instance_published() == FALSE)
				$this->set_instance_node('instance_published', ExDate::formatToDb());
			//reset archived date
			$this->set_instance_node('instance_archived', NULL);
		}
		else if($status == FALSE)
		{
			//check if reauired to set archived date first
			if($this->is_instance_published())
				$this->set_instance_node('instance_archived', ExDate::formatToDb());
			
			$this->set_instance_node('instance_published', NULL);
		}
	}*/
	
	//
	public function is_instance_published()
	{
		return (ExDate::is_date($this->get_instance_published())) ? TRUE : FALSE;
	}
	
	//
	public function is_instance_secured()
	{
		return (intval($this->get_instance_secured()) == 1) ? TRUE : FALSE;
	}
	
	private function set_trace($message)
	{		
		$trace = (is_array($trace = $this->get_property('runtime.trace'))) ? $trace : array();	
		array_push($trace, $message);
		$this->set_property('runtime.trace', $trace);	
		return $this;
	}
	
	
	// 
	public function __call($method, $args)
	{		
		
		$prefixated = ExString::prefixate($method);		
		if(isset($prefixated['prefix']) && isset($prefixated['sufix'])) 
		{			
			switch($prefixated['prefix'])
			{
				//case 'ini' :	
					//if(($root = $this->xml->getRootNode()->getNode('./' . self::XML_INI_PROPETIES)) instanceof eXmlElement == FALSE)
						//return NULL;	
				case 'abbr' :
				case 'get' :						
						return $this->get_property($prefixated['sufix']);
					
					/*if(!isset($root)) 
						if(($root = $this->coalesce($node)) == NULL)
							return (strpos($node, 'eX_assigned') !== FALSE) ? array() : NULL;						
					if($args && self::GET_VALUE_AS_ARRAY == $args[0])
					{
						$val = array();					
						foreach($root->getNodes('./' . $node) as $cnode)
						{
							$_val = array();
							$_val['value'] = $cnode->getValue();
							foreach($cnode->getAttributes() as $attrkey => $attrval)
								$_val[$attrkey] = $cnode->getAttribute($attrkey);
							array_push($val, $_val);
						}	
						//$args[1] is a regexp filter								
						return (isset($args[1])) ? (($res = ExArray::array_search($val, $args[1])) ? $res : array()) : $val;
					}
					else
					{
						$content = $root->getNode('./' . $node)->getValue();
						return ($type=='abbr') ? ExParser::abbr($content) : $content;
					}*/
				break;			
				case 'has' :
					return $this->has_property($prefixated['sufix']);
				break;			
				case 'unset' :
					return $this->unset_property($prefixated['sufix']);
				break;
			}
		}
		//none of the above worked lets calle setter
		if($this->setter instanceof ExInstanceSetter == false)
			$this->setter = new ExInstanceSetter($this);
		
		return call_user_func_array(array($this->setter, $method), $args);	
	}

}


