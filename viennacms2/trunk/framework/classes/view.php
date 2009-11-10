<?php
/**
* @package framework
* @version $Id$
* @copyright (c) 2008 viennaCMS group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

define('VIEW_PRIORITY_HIGH', 0);
define('VIEW_PRIORITY_USER', 1);
define('VIEW_PRIORITY_STOCK', 2);
define('VIEW_PRIORITY_LOW', 3);
class View implements ArrayAccess {
	private $vars;
	public $path;
	public static $searchpaths = array(
		'framework/views/' => VIEW_PRIORITY_LOW
	);
	
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
	
	public function url($data, $arguments = '', $ignore_hook = false) {
		$prefix = '';
		if (!cms::$config['rewrite']) {
			$prefix .= 'index.php/';
		}
		
		if (!is_array($data)) {
			if (is_object($data) && method_exists($data, 'to_url')) {
				$url = $data->to_url();
				$data = $url;
			}
			
			if (strpos($data, '://') === false) {
				$obj = new stdClass;
				$obj->url = cms::$router->alias_url_link($data, $arguments);
				
				if (!$ignore_hook) {
					VEvents::invoke('url.alter-output', $obj);
				}
				
				return manager::base() . $prefix . $obj->url;
			} else {
				return $data;
			}
		}
	}

	public function link($name, $url, $data = array()) {
		$attributes = $args = '';

		if (isset($data['attributes'])) {
			$attributes = $data['attributes'];
		}
		
		if (isset($data['args'])) {
			$args = $data['args'];
		}

		return '<a href="' . view::url($url, $args) . '"' . $attributes . '>' . $name . '</a>';
	}
	
	public function display() {
		ob_start();
		$view_path = $this->scan_themes($this->path);

		if (!file_exists($view_path)) {
			throw new ViewNotFoundException('View path ' . $this->path . ' does not exist!');
		}
		
		$value = include($view_path);

		if ($value == false && is_array($this->path)) {
			$path = array_shift($this->path);

			if (count($this->path)) {
				return $this->display();
			}

			$this->path[] = $path;
		}

		$contents = ob_get_contents();
		ob_end_clean();
		
		return self::clean($contents);
	}
	
	public function scan_themes($path) {
		$files = array();
		$files_0 = array();
		$files_1 = array();
		$files_2 = array();
		$files_3 = array();
		
		foreach (self::$searchpaths as $fpath => $priority) {
			$var = 'files_' . $priority;
			if (is_array($path)) {
				foreach ($path as $pth) {
					$$var = array_merge($$var, array($fpath . $pth)); // PHP won't allow $$var[] :(
				}
			} else {
				$$var = array_merge($$var, array($fpath . $path)); // PHP won't allow $$var[] :(
			}
		}
		
		$files = array_merge($files_0, $files_1, $files_2, $files_3);
		
		return cms::scan_files($files);
	}
	
	public function offsetExists($key) {
		return (isset($this->data[$key]));
	}
	
	public function offsetGet($key) {
		if (!isset($this->vars[$key])) {
			return '';	
		}
		
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