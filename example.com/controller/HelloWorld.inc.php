<?php 

class HelloWorld extends ExInstance
{
	public function __construct()
	{
		parent::__construct();		
		
		$this->set_view('HelloWorldView');	//assign view HelloWorldView.php
	}

	public function run()
	{							
		
		$this->set_main_title("My First Controller!"); //custom property, to output: {main_title} syntax
		
		$this->set_main_teaser("This is a teaser"); //custom property, to output: {main_teaser} syntax
		
		print "Hello World!";
		
	}
}

