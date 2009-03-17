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
	static $files;
	static $helpers;
	static $config;
	
	/**#@+
	* Constant defining plugin mode for objects
	*/
	const METHOD_ADD = 1;
	const METHOD_OVERRIDE = 2;
	const METHOD_INJECT = 4;
	/**#@-*/

	/**#@+
	* Constant defining plugin mode for functions
	*/
	const FUNCTION_OVERRIDE = 1;
	const FUNCTION_INJECT = 2;
	/**#@-*/
	
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
	* @todo pretty up the error page
	*/
	public static function handle_exception($exception) {
		$error_data = array(
			'code' => $exception->getCode(),
			'file' => $exception->getFile(),
			'line' => $exception->getLine()
		);
		?>
<html>
	<head>
		<title>Fatal error</title>

		<style type="text/css">
			body {
				font-family: "Segoe UI", Arial, sans-serif;
				font-size: 12px;
			}

			#header {
				position: absolute;
				top: 0px;
				left: 0px;
				height: 150px;
				width: 100%;
				right: 0px;
				background-image: url(<?php echo cms::base() ?>/index.php?vEIMG=error_background);
			}

			#error_heading {
				position: absolute;
				top: 28px;
				left: 15px;
				width: 349px;
				height: 94px;
				background-image: url(<?php echo cms::base() ?>/index.php?vEIMG=errorheading);
			}

			#viennacms {
				position: absolute;
				top: 20px;
				right: 15px;
				width: 274px;
				height: 110px;
				background-image: url(<?php echo cms::base() ?>/index.php?vEIMG=viennacmslogo);
			}

			#content {
				position: absolute;
				top: 160px;
				left: 10px;
				right: 10px;
			}

			.footer {
				font-size: 90%;
				text-align: center;
				color: #333;
			}
		</style>
	</head>
	<body>
		<!-- no language stuff, we don't even know if __() is correctly set now! -->
		<div id="header">
			<div id="error_heading"></div>
			<div id="viennacms"></div>

		</div>

		<div id="content">
		<p>A fatal error occurred during the processing of this page, and therefore, this page cannot be displayed. Please try loading this page again at a later time. If this problem persists, contact the administrator of this web site.</p>

<p><strong>Technical error data</strong> (for the administrator of this site):</p>

<p>Error type: <?php echo get_class($exception) ?><br />
Message: <?php echo $exception->getMessage() ?><br />
File: <?php echo str_replace(str_replace('\\', '/', ROOT_PATH), '', str_replace('\\', '/', $error_data['file'])) ?><br />
Line: <?php echo $error_data['line'] ?></p>

	<p class="footer">Powered by <a href="http://www.viennacms.nl/">viennaCMS</a> &copy; 2008, 2009 viennaCMS Group</p>
	</div>
	</body>
</html>
		<?php
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
	* Manager::basepath()
	* Returns the base path of the viennaCMS installation, relative to the site's document root.
	*
	* @return string path
	*/
	static function basepath() {
		$url = dirname($_SERVER['SCRIPT_NAME']);
		if (dirname($_SERVER['SCRIPT_NAME']) != '/') {
			$url .= '/';
		}
		return $url;
	}

	/**
	* Manager::base()
	* Returns the base URL of the viennaCMS installation.
	*
	* @return string absolute base URL
	*/
	static function base() {
		$url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https' : 'http';
		$url .= '://' . $_SERVER['HTTP_HOST'];
		$url .= dirname($_SERVER['SCRIPT_NAME']);
		if (dirname($_SERVER['SCRIPT_NAME']) != '/') {
			$url .= '/';
		}
		return $url;
	}
	
	/**
	* Searches for a specific file in an array, by checking all files for existence.
	*
	* @todo move to cms::
	* @param array $array
	* @return string First found filename to exist.
	*/
	static public function scan_files($array, $add_root = true) {
		foreach ($array as $file) {
			if (file_exists(ROOT_PATH . $file)) {
				// TODO: cache the result
				if ($add_root) {
					return ROOT_PATH . $file;
				} else {
					return $file;
				}
			}
		}
		
		return false;
	}

	static public function redirect($url, $parameters = array()) {
		$final_url = view::url($url);

		if ($parameters) {
			$final_url .= '?' . http_build_query($parameters);
		}

		header('Location: ' . $final_url);
		exit;
	}
}
