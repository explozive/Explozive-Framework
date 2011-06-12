<?php
//
class eXExceptionPageNotFound extends eXExceptionUrlKeyNotDefined
{
	public function __construct($message, $code = 0)
	{
		eXLog::log_process("Exception " . get_class() . ' has been thrown');
		set_exception_handler(array(get_class(), 'exception_handler'));
	}	
	
}

