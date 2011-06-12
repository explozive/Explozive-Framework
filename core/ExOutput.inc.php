<?php

class ExOutput
{			
	protected $instance;
	protected $content;
	
	public function __construct(ExInstance $instance)
	{	
		$this->instance = $instance;
	}
	
		
	
	public function start() 
	{
		return $this;
	}
	
	public function end() 
	{
		return $this;
	}
	
}
