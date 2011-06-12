<?php

class ExInstanceHtml extends ExInstance
{			
	
	public function __construct()
	{
		parent::__construct();
	}	
	
	public function run()//$properties = array()
	{	
		foreach($this->get_properties() as $key => $val)
			$$key = $val;
		
		print eval('?>'.preg_replace("/;*\s*\?>/", "; ?>", str_replace('<?=', '<?php echo ', $this->get_html_code())));
			
		//print $this->get_html_code();
		
		//$this->dump();
		
		//var_dump($this->get_runtime('source'));	
		/*	
		if($this->get_runtime('source') == 'file' && file_exists($file = $this->get_runtime('path')))
		{		
			if ((bool) @ini_get('short_open_tag') === false)
				echo eval('?>'.preg_replace("/;*\s*\?>/", "; ?>", str_replace('<?=', '<?php echo ', file_get_contents($file))));
			else
				include($file); 
		}*/
		
		return;		
	}	
	
}
