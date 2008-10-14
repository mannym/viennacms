<?php
/**
* @package framework
* @version $Id$
* @copyright (c) 2008 viennaCMS group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/
class View implements ArrayAccess {
	private $vars;
	public $path;
	
	public function __construct() {
		$this->reset_path();
	}
	
	public function reset_path() {
		$this->path  = cms::$router->parts['controller'] . '/';
		if (!empty(cms::$router->parts['action'])) {
			$this->path .= cms::$router->parts['action'] . '.php';
		} else {
			$this->path .= 'main.php';			
		}
	}
	
	public function set($var, $value) {
		$this->vars[$var] = $value;
	}
	
	public function clean($contents)
	{
		$search = array(
			'#<!-- \$(.*)+\$ -->(\s)*?(<)+#', // Newlines
		);
		$replace = array(
			'$3',
		);
		return preg_replace($search, $replace, $contents);
	}
	
	public function url($data) {
		$prefix = '';
		if (!cms::$vars['config']['rewrite']) {
			$prefix .= 'index.php/';
		}
		
		if (!is_array($data)) {
			if (strpos($data, '://') === false) {
				return manager::base() . $prefix . $data;
			} else {
				return $data;
			}
		} else {
			$url = $prefix;
			
			if (!empty($data['controller'])) {
				$url .= $data['controller'];
			}
			
			if (!empty($data['action'])) {
				$url .= '/' . $data['action'];
			}
			
			if (!empty($data['parameters'])) {
				$url .= '/' . implode('/', $data['parameters']);
			}
			
			return manager::base() . $url;
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
			'layouts/' . cms::$vars['style'] . '/' . $path,
			'views/' . $path
		);
		
		foreach (manager::$extpaths as $extension => $extpath) {
			$files[] = dirname($extpath) . '/views/' . $path;
		}
		
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