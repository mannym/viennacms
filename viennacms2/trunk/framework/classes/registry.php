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
	
	/**
	 * Constructor
	 */
	public function __construct() {
		// nothing yet
	}
	
	public function register_loader(string $path, $suffix = false) {
		$this->loader_paths[] = cms::$registry->get_loader($path, $suffix);
	}
	
	public function get_loader(string $path, $suffix = false) {
		$class = new stdClass;
		$class->folder = ROOT_PATH . $path;
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