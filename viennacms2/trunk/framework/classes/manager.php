<?php

class Manager {
	private $global;
	
	public function __construct($global) {
		$this->global = $global;
	}
	
	public function run() {
		$router = new Router($this->global);
		
		$query = $_GET['q'];
		
		// TODO: change this
		if (empty($query)) {
			$query = 'index/main';
		}
		
		$parts = $router->route($query);
		$action = (!empty($parts['action'])) ? $parts['action'] : 'main';
		
		$controller = $this->get_controller($parts['controller']);
		$controller->arguments = explode('/', $parts['params']);
		$controller->$action();
	}
	
	public function get_controller($name) {
		include(ROOT_PATH . 'controllers/' . strtolower($name) . '.php');
		$class_name = ucfirst(strtolower($name)) . 'Controller';
		
		return new $class_name($this->global);
	}
}