<?php
/**
* viennaCMS type registry class 
* 
* @package viennaCMS2
* @version $Id$
* @copyright (c) 2009 viennaCMS group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

class Registry {
	private $loader_paths = array();
	private $registered_types = array();
	
	/**
	 * Constructor
	 */
	public function __construct() {
		// nothing yet
	}
	
	public function register_loader(string $path, $suffix = false) {
		$this->loader_paths[] = cms::$registry->get_loader($path, $suffix);
	}
	
	public function register_type(string $type_name) {
		if (!class_exists($type_name)) {
			throw new InvalidArgumentException("Type $type_name does not exist.");
		}
		
		$this->registered_types[] = $type_name;
	}
	
	public function get_type($name, $suffix) {
		$full_name = strtolower($name . $suffix);
		
		foreach ($this->registered_types as $type) {
			$type = strtolower($type);
			
			if ($full_name == $type) {
				return new $type;
			}
		}
		
		return false;
	}
	
	public function get_types($suffix) {
		$return = array();
		
		foreach ($this->registered_types as $type) {
			if (string::ends_with(strtolower($type), strtolower($suffix))) {
				$return[str_replace(strtolower($suffix), '', strtolower($type))] = $type;
			}
		}
		
		return $return;
	}
	
	public function get_loader(string $path, $suffix = false) {
		$class = new stdClass;
		$class->folder = VIENNACMS_PATH . $path;
		$class->class_suffix = $suffix;
		
		return $class;
	}
	
	/**
	 * Autoload callback, for use with spl_register_autoload.
	 * 
	 * @param $class string class name
	 * @return void
	 */
	public function autoload(string $class) {
		$test = new stdClass();
		$test->class = $class;
		VEvents::invoke('core.autoload-class-name', $test);
		$class = $test->class;
		
		$class = strtolower($class);
		
		$classes = array($class);
		
		if ($class{0} == 'v') {
			$classes[] = substr($class, 1);
		}
		
		foreach ($classes as $class) {		
			foreach ($this->loader_paths as $item) {
				$classname = $class;
				
				if ($item->class_suffix) {
					if (!string::ends_with($class, $item->class_suffix)) {
						continue;
					}
					
					$classname = str_replace($item->class_suffix, '', $class);
				}
				
				$filename = $item->folder . '/' . $classname . '.php';
				
				if (file_exists($filename)) {
					cms::vinclude($filename);
					return;
				}
			}
		}
	}
}