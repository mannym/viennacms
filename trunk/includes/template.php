<?php
/**
* viennaCMS templating system.
* "We make it this way, and use this template." -- me :)
* 
* @package viennaCMS
* @author viennainfo.nl
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

/**
* @ignore
*/

if (!defined('IN_VIENNACMS')) {
	exit;
}

/**
* The holy templating class :D
* 
* @package template 
*/

class template {
	static private $instance;
	
	private $name; 
	public $root;
	
	private $handles;
	private $vars;
	
	/**
	* Return an instance of template.
	*/
	static function getnew() {
		if (!isset(self::$instance)) {
			self::$instance = new template;
			if (method_exists(self::$instance, 'initialize'))
			{
				self::$instance->initialize();
			}
		}
		return self::$instance;
	}
	
	/**
	* Initialize the template class. 
	*/

	public function initialize($template = 'default') {
		$this->name = $template;
		$this->root = ROOT_PATH . 'styles/' . $this->name . '/';
		
		if (!file_exists($this->root)) {
			trigger_error('Template root does not exist', E_USER_ERROR);
		}
		
		$this->assign_vars(array(
			'stylesheet' => 'styles/' . $this->name . '/style.css'
		));
	}
	
	/**
	* Assign a filename to a handle. 
	*/

	public function set_filename($handle, $name) {
		$filename = $this->root . $name;
		
		if (!file_exists($filename)) {
			trigger_error('Template file does not exist: ' . $filename, E_USER_ERROR);
		}
		
		$this->handles[$handle] = $this->root . $name;
	}
	
	public function assign_vars($array) {
		foreach ($array as $key => $value) {
			$this->assign_var($key, $value);
		}
	}
	
	private function assign_var($var, $value) {
		$this->vars[$var] = $value;
	}
	
	public function display($handle) {
		foreach ($this->vars as $key => $value) {
			$$key = $value;
		}
		
		@include($this->handles[$handle]);
	}
	
	public function assign_display($handle, $var = '', $return = true) {
		ob_start();
		$this->display($handle);
		$contents = ob_get_contents();
		ob_end_clean();
		
		if ($return) {
			return $contents;
		} else {
			$this->assign_var($var, $contents);
		}
	}
}
?>