<?php
/**
* viennaCMS templating system.
* "We make it this way, and use this template." -- me :)
* 
* @package viennaCMS
* @author viennacms.nl
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
	public $vars;
	public $private_vars;
	
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

	public function set_alt_filename($handle, $names) {
		foreach ($names as $name) {
			if (file_exists($this->root . $name)) {
				$filename = $this->root . $name;
				break;
			}
		}
		
		if (empty($filename)) {
			trigger_error('Multiple template files do not exist', E_USER_ERROR);
		}
		
		$this->handles[$handle] = $this->root . $name;
	}
	
	public function assign_vars($array) {
		foreach ($array as $key => $value) {
			$this->assign_var($key, $value);
		}
	}

	public function assign_priv_vars($handle, $array) {
		foreach ($array as $key => $value) {
			if (!$this->private_vars[$handle]) {
				$this->private_vars[$handle] = array();
			}
			$this->private_vars[$handle][$key] = $value;
		}
	}
	
	private function assign_var($var, $value) {
		$this->vars[$var] = $value;
	}
	
	public function display($handle, $process = true) {
		foreach ($this->vars as $key => $value) {
			$$key = $value;
		}
		
		if (is_array($this->private_vars[$handle])) {
			foreach ($this->private_vars[$handle] as $key => $value) {
				$$key = $value;
			}
		}
		
		ob_start();
		@include($this->handles[$handle]);
		$contents = ob_get_contents();
		ob_end_clean();
		
		if ($process) {
			$clen = strlen($contents);
			
			if (!defined('DEBUG')) {
				$contents = str_replace(array("\t", "\n", "\r"), '', $contents);
				$mlen = strlen($contents);
			} else {
				$mlen = $clen;
			}
			
			$contents .= <<<HTML

<!--
	******************************************
	This page is generated by viennaCMS, 
	an free GPLed content management system.
	
	Information is available at the following
	site: http://www.viennainfo.nl/
	
	Page size (non-minified)	: $clen
	Page size (minified)		: $mlen
	******************************************
-->	
HTML;
		}
		
		echo $contents;
	}
	
	public function assign_display($handle, $var = '', $return = true) {
		ob_start();
		$this->display($handle, false);
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