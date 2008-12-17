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
		$name = strtolower($name);

		$files = array();
		
		foreach (self::$searchpaths as $path) {
			$files[] = $path . $name . '.php';
		}
		
		//foreach (self::$extpaths as $extension => $path) {
		//	$files[] = dirname($path) . '/controllers/' . $name . '.php';
		//}
		
		$filename = cms::scan_files($files);
		
		if (file_exists($filename)) {
			include_once($filename);
			return true;
		}
		
		return false;
	}
}
?>