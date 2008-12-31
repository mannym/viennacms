<?php
/**
* cms
* The main management/storage class, which keeps variables, and has utility functions.
* 
* @package viennaCMS2
* @version $Id$
* @access public
*/
abstract class cms {
	static $db;
	static $vars;
	static $user;
	static $manager;
	static $router;
	static $layout;
	static $cache;
	static $plugins;
	
	public static $instances = null;
	
	private function __construct() { } // make derivative classes impossible to instantiate
	
	/**
	* cms::get_instance()
	* 
	* Get a property instance.
	* 
	* @param string $name Name of the object to retrieve.
	* 
	* @return mixed property value
	*/
	
	public static function get_instance($name) {
		if (!self::registered($name)) {
			return self::register($name);
		}
		
		if (!property_exists('cms', $name)) {
			return self::$instances[$name];
		} else {
			return self::$$name;
		}
	}
	
	/**
	* cms::register()
	* Registers one of the required objects.
	* 
	* @param string $name Object name.
	* @param string $class The name of the class, if it can not be automatically determined.
	* @return void
	*/
	public static function register($name, $class = false) {
		if (self::registered($name)) {
			return self::get_instance($name);
		}
		
		if ($class === false) {
			$class = $name;
		}
		
		$reflection = new ReflectionClass($class);

		if (!$reflection->isInstantiable()) {
			throw new Exception('A class needs to be instantiable.');
		}
		
		if (!property_exists('cms', $name)) {
			self::$instances[$name] = $reflection->newInstance();
		} else {
			self::$$name = $reflection->newInstance();
		}
		
		return self::get_instance($name);
	}
	
	/**
	* cms::assign()
	* Assign an object to a variable. This one is more like the pre-M1 register().
	* 
	* @param string $name Object name.
	* @param mixed $object The object to add.
	* @return void
	*/
	public static function assign($name, $object) {
		if (self::registered($name)) {
			return self::get_instance($name);
		}
		
		if (!property_exists('cms', $name)) {
			self::$instances[$name] = $object;
		} else {
			self::$$name = $object;
		}
		
		return self::get_instance($name);
	}
	
	public static function registered($name) {
		if (property_exists('cms', $name)) {
			if (!empty(self::$$name)) {
				return true;
			}
		} else {
			if (!empty(self::$instances[$name])) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	* cms::display_allowed()
	* Utility function, asks all node hooks if a specific node may be located under another node.
	* 
	* @param string $type The type of check that needs to be done. Seemingly, only 'this_under_other' is implemented now.
	* @param Node $node The 'this' node to be checked.
	* @param mixed $other The 'other' node, may be false.
	* @return bool success value
	*/
	public static function display_allowed($type, $node, $other = false) {
		$results = manager::run_hook_all('display_allowed', $type, $node, $other);
		
		foreach ($results as $result) {
			if ($result == false) {
				return false;
			}
		}
		
		return true;
	}
	
	/**
	* cms::get_admin_tree()
	* Gets a tree structure for all nodes - for use in the ACP.
	* 
	* @todo make this a more generic function, which can also be used outside of the ACP.
	* @param string $node_link_template Template for the link target. May contain the following replacement tags: %node_id, %node_type
	* @return string tree HTML
	*/
	public static function get_admin_tree($node_link_template, $selected = false) {
		$node = new Node();
		$node->node_id = 0;
		return self::_get_admin_tree($node, '', $node_link_template, $selected);
	}
	
	/**
	* cms::_get_admin_tree()
	* internal function for get_admin_tree()
	*/
	private static function _get_admin_tree($node, $list = '', $nlt = '', $selected = false) {
		if ($node->node_id != 0) {
			$pnlt = str_replace(
				array('%node_id', '%node_type'),
				array($node->node_id, $node->type),
				$nlt
			);
			
			$class = '';
			
			if (is_numeric($selected) && $node->node_id == $selected) {
				$class = ' selected';
			}
			
			$list .= '<li id="node-' . $node->node_id . '"><a href="' . $pnlt . '" class="' . $node->type . $class . '">' . $node->title . '</a>' . "\r\n";			
		}
			
		$nodes = $node->get_children();

		$my_id = $node->node_id;
			
		if ($nodes) {
			$list .= '<ul>';
			foreach ($nodes as $node) {
				$list = self::_get_admin_tree($node, $list, $nlt, $selected);
			}
			$list .= '</ul>';
		}
		
		$list .= '</li>';
		return $list;
	}
	
	/**
	* @todo pretty up the error page
	*/
	public static function handle_exception($exception) {
		$error_data = array(
			'code' => $exception->getCode(),
			'file' => $exception->getFile(),
			'line' => $exception->getLine()
		);
		
		$string = base64_encode(str_rot13(serialize($error_data)));
		$lines = implode("\n", str_split($string, 60));
		
		echo '<html><body>';
		echo '<h1>viennaCMS: critical error</h1>';
		echo $exception->getMessage();
		echo '<h2>Debug information (for developers)</h2><pre>';
		echo $lines;
		echo '</pre></body></html>';
	}
	
	public static function autoload($class_name) {
		// enable the blueprint
		$filename = ROOT_PATH . 'blueprint/classes/' . strtolower($class_name) . '.php';
	
		if (file_exists($filename)) {
			include_once($filename);
			return true;
		}
		
		$filename = ROOT_PATH . 'blueprint/models/' . strtolower($class_name) . '.php';
		
		if (file_exists($filename)) {
			include_once($filename);
			return true;
		}
	}
	
	/**
	* Searches for a specific file in an array, by checking all files for existence.
	*
	* @todo move to cms::
	* @param array $array
	* @return string First found filename to exist.
	*/
	static public function scan_files($array) {
		foreach ($array as $file) {
			if (file_exists(ROOT_PATH . $file)) {
				// TODO: cache the result
				return ROOT_PATH . $file;
			}
		}
		
		return false;
	}
}
