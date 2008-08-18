<?php

class Manager {
	private $global;
	
	public function __construct($global) {
		$this->global = $global;
	}
	
	public function run() {
		$router = new Router($this->global);
		
		$query = $_GET['q'];
		$parts = $router->route($query);
		
		var_dump($parts);
	}
}