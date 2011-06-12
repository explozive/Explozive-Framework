<?php
//
class eXExceptionUrlKeyNotDefined extends Exception
{
	public function __construct($message, $code = 0)
	{
		eXLog::log_process("Exception " . get_class() . ' has been thrown');
		set_exception_handler(array(get_class(), 'exception_handler'));
	}	
	
	public static function exception_handler($exception) 
	{
		$uuid = eXQueryInstance::domain($_SERVER['SERVER_NAME']);
		
		if($uuid && eXUtils::is_uuid($uuid)) 
		{
			eXLog::log_process("Domain instance is found: $uuid for " . $_SERVER['SERVER_NAME']);
			//lets init domain
			$domain = eXInstance::init($uuid);				
			if($domain instanceof eXInstance) 
			{
				if(eXUtils::is_uuid($uuid = $domain->get_index_instance()))
				{					
					//init index page					
					eXLog::log_process("Domain's index page is set to: " . $uuid);	
					$index = eXInstance::init($uuid);
					if($index instanceof eXInstance) 
					{
						eXGlobals::set('eX_requested_instance', $index);
						print $index->process();
					}	
				}
				else
					eXLog::log_process("Domain's index page is not set!");	
				//	
			}				
		}	else
			eXLog::log_process("Domain is not found for " . $_SERVER['SERVER_NAME']);	
		
		//dump logger, if u wish to disable logger please comment the line below
		eXLog::dump();

	}
	
}

