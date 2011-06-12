<?php

class ExOutputHtml extends ExOutput
{			
	private $headers = array();
	
	
	public function start()
	{
		//
		header("Content-Type: text/html; charset=UTF-8");
		
		
		$this->content = ExParser::parse_url_key($this->instance->process());
		
		return $this;
	}
	
	public function end() 
	{
		$content = $this->content;
		// 
		if (FALSE == preg_match('/<html([^>]*)>[\s\S]*<\/html>/i', $content)) 
			$content = sprintf("<html xmlns=\"http://www.w3.org/1999/xhtml\">\n<head>\n</head>\n<body>\n%s\n</body>\n</html>\n", $content);

		//
		if (FALSE == preg_match('/<head([^>]*)>[\s\S]*<\/head>/i', $content)) 
			$content = preg_replace('/<html([^>]*)>/i', "<html$1>\n<head></head>\n", $content);

		//
		if (FALSE === stripos(trim($content), '<!DOCTYPE html')) 
			$content = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n" . $content;

		//at the very last lets parse content and remove injected additional <head..s
		if (TRUE == preg_match('/<html[^>]*>([\s\S]*)<\/html>/i', $content, $regs)) 
		{
			$count = 0;		
			//
			while(TRUE == preg_match('/(<!DOCTYPE\s+html[^>]*>|<html[^>]*>)([\s\S]*)<\/html>/i', $regs[1], $regs2)) 
			{							
				
				
				if(preg_match('/<body[^>]*>([\s\S]*)<\/body>/i', $regs2[0], $regbody)) 
				{
					$content = str_replace($regs2[0], $regbody[1], $content);
					$regs[1] = $regbody[1];
										//var_dump($content);
					$count++;
					if($count > 2) break;					
				}	
				else //to prevent dead loop
					$regs[1] = "";
							
			}
		}
	
		//lets inject inline editing feature if required
		//if(ExGlobals::has('cp-inline-editing') == TRUE)
		/*if(isset($_COOKIE['EX_CP_EDIT_INLINE']) && $_COOKIE['EX_CP_EDIT_INLINE'] == 1 && ExGlobals::has('cp-inline-editing') == FALSE)
		{
			$this->set_html_header('24656198-adb9-007f-a5b4-3409c301b682');	
		}		*/
		
			foreach($this->headers as $ref)
				$content = str_replace('</head>', $ref . "\n</head>", $content);
		
		print $content;		
		return $this;	
	}
	
	/*
	private function set_header_assignments(eXInstance $instance) 
	{	
		$items = $instance->get_eX_assigned(eXInstance::GET_VALUE_AS_ARRAY, 'type==\'eX-stylesheet\' || type==\'eX-javascript\'');
		foreach($items as $item)
			$this->set_html_header($item['instance_uuid']);	
	}	*/
	
	public function set_html_header($str) 
	{		
		if(ExUuid::is_uuid($str))
		{
			$instance = ExInstance::init($str);
		}
		else
			$header_str = $str;
		
		
		//$instance->dump();
		if($instance instanceof eXInstanceHtmlEmbeddable)
		{			
			//var_dump($str);
			$header_str = $instance->get_html_tag();			
			//if($instance->get_type_uuid() == EX_UUID_TYPE_STYLESHEET)
				//$header_str = sprintf('<link rel="stylesheet" type="text/css" href="%s">', $instance->get_relative_url());
			//else if($instance->get_type_uuid() == EX_UUID_TYPE_JAVASCRIPT)
				//$header_str = sprintf('<script type="text/javascript" src="%s"></script>', $instance->get_relative_url());
		}

		if(isset($header_str))
			if(in_array($header_str, $this->headers) == false)
			 	array_push($this->headers, $header_str);
	}
	
}
