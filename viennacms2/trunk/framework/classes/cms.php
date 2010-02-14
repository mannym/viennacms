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
	static $registry;
	
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
	
	public static function ext($extension_name) {
		try {
			return Manager::load_extension($extension_name);
		} catch (Exception $e) {
			return false;
		}
	}
	
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
		//$results = manager::run_hook_all('display_allowed', $type, $node, $other);
		$results = VEvents::invoke('node.check-allowed', $type, $node, $other);
		
		foreach ($results as $result) {
			if ($result == false) {
				return false;
			}
		}
		
		return true;
	}
	
	/**
	* Shows a page with the specified error message.
	* 
	* @param string $message message
	* @param string $title page title for the message
	* @return void
	*/
	public static function show_info(string $message, $title = '') {
		if ($title) {
			cms::$layout->view['title'] = $title;
		}
		$content = $message;			
		cms::$layout->page($content);
		echo cms::$layout->view->display();
		exit;
	}
	
	private static $loaded_files = array();
	
	public static function vinclude($filename, $once = false) {
		// do pre-compilation
		
		$unique_name = str_replace("\\", '/', strtolower($filename));
		
		if ($once) {
			if (in_array($unique_name, self::$loaded_files)) {
				return;
			}
		}
		
		self::$loaded_files[] = $unique_name;
		
		if (!empty(cms::$cache)) {
			// get the cache
			$object_cache = cms::$cache->get('compiler_results');
			$file_hash = md5(str_replace(VIENNACMS_PATH, '', $filename));
			
			if ($object_cache) {
				// does the cache contain this file?
				if (!empty($object_cache[$file_hash])) {
					// check if the file is still the same...
					$stored_mtime = $object_cache[$file_hash]['content_mtime'];
					$file_mtime = filemtime($filename);
					
					if ($file_mtime == $stored_mtime) {
						// and check if it's really known :)
						if ($object_cache[$file_hash]['result'] == true) {
							return include($filename); // note the return, we want the return value as well
						}
					}
				}
			}
			
			// okay, we need to do a precompilation ourselves.
			$file_data = file_get_contents($filename);
			
			ob_start(); // to catch parse errors
			$value = eval('?>' . $file_data); // eval is sometimes evil, that's the reason of the checks above. :)
			$content = ob_get_contents();
			ob_end_clean(); // we'll use the output later on
			
			$okay = true;
			
			if ($value === false) {
				// gotta love implementation details
				$pattern = '@<b>Parse error</b>:\s*(.+?) in <b>(.+?) : eval\(\)\'d code</b> on line <b>(.+?)</b><br />@i';
				
				if (preg_match($pattern, $content, $matches)) {
					$okay = false;
				}
			}
			
			if (!$object_cache) {
				$object_cache = array();
			}
			
			$object_cache[$file_hash] = array(
				'content_mtime' => filemtime($filename),
				'result' => $okay
			);
			
			cms::$cache->put('compiler_results', $object_cache);
			
			if ($okay) {
				echo $content;
				return $value;
			} else {
				throw new Exception(sprintf('An error occurred during parsing of source code.<br />Parse error: %s in %s on line %d', $matches[1], str_replace(VIENNACMS_PATH, '', $filename), $matches[3]));
			}
		}
				
		return include($filename);
	}

	public static function handle_exception($exception) {
		header('HTTP/1.1 503 Service Unavailable');
		
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
File: <?php echo str_replace(str_replace('\\', '/', VIENNACMS_PATH), '', str_replace('\\', '/', $error_data['file'])) ?><br />
Line: <?php echo $error_data['line'] ?></p>

	<p class="footer">Powered by <a href="http://www.viennacms.nl/">viennaCMS</a> &copy; 2008, 2009 viennaCMS Group</p>
	</div>
	</body>
</html>
		<?php
	}
	
	public static function autoload($class_name) {
		// enable the blueprint
		$filename = VIENNACMS_PATH . 'blueprint/classes/' . strtolower($class_name) . '.php';
	
		if (file_exists($filename)) {
			include_once($filename);
			return true;
		}
		
		$filename = VIENNACMS_PATH . 'blueprint/models/' . strtolower($class_name) . '.php';
		
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
			if (file_exists(VIENNACMS_PATH . $file)) {
				// TODO: cache the result
				if ($add_root) {
					return VIENNACMS_PATH . $file;
				} else {
					return $file;
				}
			}
		}
		
		return false;
	}
	
	static public function check_upgrade() {
		// okay, it's time. time to check if a upgrade is needed.
		// the config code should be initialized now
		
		$database_revision = 1; // if we're not upgraded enough, try this
		
		if (isset(cms::$config['database_revision'])) {
			$database_revision = (int)(string)cms::$config['database_revision']; // typecasting to be sure
		}
		
		$database_version = 0;
		
		include(VIENNACMS_PATH . 'blueprint/version.php');
		
		if ($database_version > $database_revision) {
			// okay, we're out of date... too bad for that problem
			// do not initialize any further, we want the interim upgrade tool. :)

			define('MINIMAL', true); // stop the rest of the manager/core to run strange tricks
			cms::$vars['upgrade_from'] = $database_revision;
			cms::$vars['upgrade_to'] = $database_version;
			$manager = new Manager();
			$manager->run('install/update');
			exit;
		}
	}
	
	static public function log($log_source, $log_message, $log_type) {
		// TODO: figure out something for localization
		$item = VLogItem::create('VLogItem');
		$item->log_source = $log_source;
		$item->log_type = $log_type;
		$item->log_message = $log_message;
		$item->log_time = time();
		$item->log_user = (!empty(cms::$user->user->user_id)) ? cms::$user->user->user_id : 0;
		$item->write();
	}

	static public function redirect($url, $parameters = array()) {
		$final_url = view::url($url);

		if ($parameters) {
			$final_url .= '?' . http_build_query($parameters);
		}

		header('Location: ' . $final_url);
		exit;
	}
	
	static public function format_date($timestamp) {
		// FIXME: actually allow preferences
		return date('Y-m-d G:i', $timestamp);	
	}
	
	static public function class_alterations($class) {

	}
}
