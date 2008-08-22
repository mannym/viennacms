<?php

class Router {
	private $global;
	public $routes;
	public $parts;
	
	public function __construct($global) {
		$this->global = $global;
		
		include(ROOT_PATH . 'framework/config/router.php');
		$this->routes = $routes;
	}
	
	public function route($query) {
		foreach ($this->routes as $regex => $mapping) {
			if (preg_match($regex, $query, $regs)) {
				$parts = array();
				
				foreach ($mapping as $key => $part) {
					$parts[$part] = $regs[$key + 1];
				}
				
				$this->parts = $parts;
				break;
			}
		}
	}
}