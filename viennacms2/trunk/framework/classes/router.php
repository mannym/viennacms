<?php

class Router {
	public $routes;
	public $parts;
	public $query;
	public $aliases = array();
	
	public function __construct() {
		include(ROOT_PATH . 'framework/config/router.php');
		$this->routes = $routes;
		
		$alias = new URL_Alias();
		$aliases = $alias->read();
		
		foreach ($aliases as $alias) {
			$this->aliases[$alias->alias_url] = $alias->alias_target;
		}
	}
	
	public function route($query) {
		$this->query = $query;
		$this->query = $this->check_alias();
		$this->match_parts();
	}
	
	private function check_alias($url = '', $depth = 5) {
		$url = (!empty($url)) ? $url : $this->query;
		
		if (isset($this->aliases[$url]) && $depth > 0) {
			$depth--;
			$url = $this->check_alias($this->aliases[$url], $depth);
		}
		
		return $url;
	}
	
	private function match_parts() {		
		foreach ($this->routes as $regex => $mapping) {
			if (preg_match($regex, $this->query, $regs)) {
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