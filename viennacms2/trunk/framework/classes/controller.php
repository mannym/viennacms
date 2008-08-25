<?php
class Controller {
	protected $global;
	public $arguments;
	public $view;
	
	public function __construct($global) {
		$this->global = $global;
	}
}
?>