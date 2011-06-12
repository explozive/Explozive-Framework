<?php

class ExInstanceDocument extends ExInstance
{			
	public function __construct($identity = NULL)
	{
		parent::__construct($identity);
	}
	
	public function run()
	{
		$file = $this->get_file_path();
		if(file_exists($file) == false)
			throw new ExExceptionFileNotFound("File not found!");	
		
		ExDocument::download(
			$file, 
			$this->get_file_name(),
			ExString::coalesce($this->get_content_disposition(), 'attachment'),
			ExString::coalesce($this->get_content_type(), 'application/octet-stream')
		);
		//script will exit right after that
	}
	
	//
	public function get_file_name()
	{
		return (is_array($file = $this->get_pdf()) ? $file['name'] : $this->get_instance_name()); 
	}	
	
	//
	public function get_tmp_file_name()
	{
		return (is_array($file = $this->get_pdf()) ? $file['tmp_name'] : null); 
	}	
	
	//
	public function get_file_path()
	{
		return ExPath::storage($this) . (($file = $this->get_tmp_file_name()) ? $file : 'uknown'); 
	}	
	
}
