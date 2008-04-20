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
	* 
	* @return template $instance
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
	* 
	* @param string $template: The template folder. 
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
	* 
	* @param string $handle: The handle (like body)
	* @param string $name: the file.
	*/

	public function set_filename($handle, $name) {
		$filename = $this->root . $name;
		
		if (!file_exists($filename)) {
			trigger_error('Template file does not exist: ' . $filename, E_USER_ERROR);
		}
		
		$this->handles[$handle] = $this->root . $name;
	}

	/**
	 * Set alternative filenames
	 * 
	* @param string $handle: The handle (like body)
	* @param string $name: the file.
	 */
	
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
	
	/**
	 * Assign vars
	 * 
	 * @param array $array: The array with vars
	 */
	
	public function assign_vars($array) {
		foreach ($array as $key => $value) {
			$this->assign_var($key, $value);
		}
	}

	/**
	 * Assign private vars, just for one handle.
	 * 
	 * @param string $handle: The handle
	 * @param string $array: the array with vars.
	 */
	
	public function assign_priv_vars($handle, $array) {
		foreach ($array as $key => $value) {
			if (!$this->private_vars[$handle]) {
				$this->private_vars[$handle] = array();
			}
			$this->private_vars[$handle][$key] = $value;
		}
	}
	
	/**
	 * Assign one value to a var.
	 * 
	 * @param string $var: the variable.
	 * @param string $value: the value.
	 */
	
	private function assign_var($var, $value) {
		$this->vars[$var] = $value;
	}
	
	/**
	 * Display the page
	 * 
	 * @param string $handle: The handle.
	 * @param bool $process: minify the page? (if DEBUG and/or DEBUG_EXTRA is defined, this will not happen.
	 */
	
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
		$content = ob_get_contents();
		ob_end_clean();
		
		if ($process) {			
			if (!defined('DEBUG') && !defined('DEBUG_EXTRA')) {
				$content = str_replace(array("\t", "\n", "\r"), '', $content);
			}
			$content = "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t" . $content . "\t";
			$content .= <<<HTML
<!--

	**************************************************************
	This page is generated by viennaCMS, a free open source
	content	management system. viennaCMS has nothing to do with
	the content of this site.
	
	Information is available at the following
	site: http://www.viennacms.nl/
	**************************************************************
HTML;
		$content .= "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t -->";
	}
		echo $content;
	}
	
	/**
	 * Assign the display
	 * 
	 * @param $handle: the handle
	 * @param $var: If you want to assign the contents of the handle to a var, use this.
	 * @param bool $return: Return it, or assign it to a var?
	 * @return mixed contents or succes 
	 */
	
	public function assign_display($handle, $var = '', $return = true) {
		ob_start();
		$this->display($handle, false);
		$contents = ob_get_contents();
		ob_end_clean();
		
		if ($return) {
			return $contents;
		} else {
			$this->assign_var($var, $contents);
			return true;
		}
	}
}
?>