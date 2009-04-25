<?php
class Controller {
	public $arguments;
	public $view;
	
	public static $searchpaths = array(
		'framework/controllers/'
	);
	
	static public function autoload($class_name) {
		if (substr($class_name, -10) == 'Controller') {
			return self::load(substr($class_name, 0, -10));
		}
	}
	
	static public function load($name) {
		$classname = $name . 'Controller';
		
		if (!class_exists($classname)) {
			return false;
		}
		
		return new $classname();
	}
}
?>