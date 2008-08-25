<?php
/**
* @package framework
* @version $Id$
* @copyright (c) 2008 viennaCMS group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/
class View implements ArrayAccess {
	private $global;
	private $vars;
	public $path;
	
	public function __construct($global) {
		$this->global = $global;
		
		$this->path  = $this->global['router']->parts['controller'] . '/';
		$this->path .= $this->global['router']->parts['action'] . '.php';
		
	}
	
	public function set($var, $value) {
		$this->vars[$var] = $value;
	}
	
	public function clean($contents)
	{
		$search = array(
			'#<!-- \$(.*)+\$ -->(\s)*?(<!DOCTYPE)+#', // Newlines
		);
		$replace = array(
			'$3',
		);
		return preg_replace($search, $replace, $contents);
	}
	
	public function url($data) {
		if (!is_array($data)) {
			if (strpos($data, '://') === false) {
				return '?q=' . $data;
			} else {
				return $data;
			}
		} else {
			$url = '';
			
			if (!empty($data['controller'])) {
				$url .= '?q=' . $data['controller'];
			}
			
			if (!empty($data['action'])) {
				$url .= '/' . $data['action'];
			}
			
			if (!empty($data['parameters'])) {
				$url .= '/' . implode('/', $data['parameters']);
			}
			
			return $url;
		}
	}
	
	public function display() {
		ob_start();
		$view_path = $this->scan_themes($this->path);

		if (!file_exists($view_path)) {
			trigger_error('View path does not exist!', E_USER_ERROR);
		}
		
		include($view_path);
		$contents = ob_get_contents();
		ob_end_clean();
		
		return self::clean($contents);
	}
	
	public function scan_themes($path) {
		$files = array(
			'layouts/' . $this->global['style'] . '/' . $path,
			'views/' . $path
		);
		
		return Manager::scan_files($files);
	}
	
	public function offsetExists($key) {
		return (isset($this->data[$key]));
	}
	
	public function offsetGet($key) {
		return $this->vars[$key];
	}

	public function offsetSet($key, $value) {
		$this->set($key, $value);
	}

	public function offsetUnset($key) {
		unset($this->data[$key]);
	}
}
?>