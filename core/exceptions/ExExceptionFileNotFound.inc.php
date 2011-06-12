<?php
//
class ExExceptionFileNotFound extends Exception
{
	public function __construct($message, $code = 0)
	{
		//eXLog::log_process("Exception " . get_class() . ' has been thrown');
		set_exception_handler(array(get_class(), 'exception_handler'));
	}	
	
	public static function exception_handler($exception) 
	{
	
		var_dump("not found");
	}
	
}

