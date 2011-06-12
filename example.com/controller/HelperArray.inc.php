<?php if(!defined('EX_VERSION')) exit('Access denied!');

class HelperArray extends ExInstance
{
	
	
	/**
	 * array_search
	 *
	 * to try this example on your own call this controller as:
	 * http://yourdomain.com/example.com/index.php/helper-array/array_search
	 *
	 */	
	public function array_search()
	{							
		$tosearch = array(
			0 => array('color' => 'red', 'shape' => 'circle'),
			1 => array('color' => 'red', 'shape' => 'square'),
			2 => array('color' => 'blue', 'shape' => 'circle'),
			3 => array('color' => 'blue', 'shape' => 'square')
		);
		$result = ExArray::array_search($tosearch, "shape=='circle' AND color=='blue'", true);
		var_dump($result);		
		
		/*
		the example outputs the following
		
		array(1) {
		  [0]=>
		  array(2) {
		    ["color"]=>
		    string(4) "blue"
		    ["shape"]=>
		    string(6) "circle"
		  }
		}
		*/				
	}
	
	
	
	/**
	 * array_assoc_to_numeric
	 *
	 * to try this example on your own call this controller as:
	 * http://yourdomain.com/example.com/index.php/helper-array/array_unique
	 *
	 */	
	public function array_unique()
	{							
		$source = array(
			0 => array('red'),
			1 => array('blue'),
			2 => array('red')
		);
		$array = ExArray::array_unique($source);
		var_dump($array);		
		
		/*
		the example outputs the following
		
		array(2) {
		  [0]=>
		  array(1) {
		    [0]=>
		    string(3) "red"
		  }
		  [1]=>
		  array(1) {
		    [0]=>
		    string(4) "blue"
		  }
		}		
		*/				
	}
	
	
	
	/**
	 * array_to_numeric
	 *
	 * to try this example on your own call this controller as:
	 * http://yourdomain.com/example.com/index.php/helper-array/array_to_numeric
	 *
	 */	
	
	public function array_to_numeric()
	{							

		$source = array(
			0 => array('red', 'bread', 'wiki'),
			1 => array('blue', 'glue', 'wall'),
		);
		$array = ExArray::array_to_numeric($source, 1);
		var_dump($array);		
		
		/*
		the example outputs the following
		
		array(2) {
		  [0]=>
		  string(5) "bread"
		  [1]=>
		  string(4) "glue"
		}
		
		*/				
	}
	
	
	/**
	 * array_to_assoc
	 *
	 * to try this example on your own call this controller as:
	 * http://yourdomain.com/example.com/index.php/helper-array/array_to_assoc
	 *
	 */	
	
	public function array_to_assoc()
	{							

		$source = array(
			0 => array('red', 'bread'),
			1 => array('blue', 'glue'),
		);
		$array = ExArray::array_to_assoc($source);
		var_dump($array);		
		
		/*
		the example outputs the following
		
		array(2) {
		  ["red"]=>
		  string(5) "bread"
		  ["blue"]=>
		  string(4) "glue"
		}
		
		*/				
	}
	
	
}

