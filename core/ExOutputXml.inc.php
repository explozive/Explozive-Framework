<?php

class ExOutputXml extends ExOutput
{			
	public function start()
	{
		//
		header("Content-Type: text/xml; charset=UTF-8");
		
		
		$this->content = ExParser::parse_url_key($this->instance->process());
		return $this;
	}
	
	
	public function end() 
	{
		print $this->content;	
		return $this;
	}
	
}
