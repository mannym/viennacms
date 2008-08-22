<?php
class View implements ArrayAccess {
	private $global;
	private $vars;
	
	public function __construct($global) {
		$this->global = $global;
	}
	
	public function set($var, $value) {
		$this->vars[$var] = $value;
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
		$view_path = ROOT_PATH . 'views/';
		$view_path .= $this->global['router']->parts['controller'] . '/';
		$view_path .= $this->global['router']->parts['action'] . '.php';
		
		if (!file_exists($view_path)) {
			trigger_error('View path does not exist!', E_USER_ERROR);
		}
		
		foreach ($this->vars as $key => $value) {
			$$key = $value;
		}
		
		include($view_path);
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