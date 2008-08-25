<?php

class Manager {
	private $global;
	public static $extpaths = array();
	
	/**
	 * Constructor of Manager
	 *
	 * @param GlobalStore $global
	 */
	public function __construct($global) {
		$this->global = $global;
		$this->global['manager'] = $this;
		// TODO: dynamically load this
		self::$extpaths['core'] = 'extensions/core/core.ext.php';
	}
	
	/**
	 * Runs the page.
	 *
	 * @param string $query URL to parse and run, if empty, the current URL is used.
	 */
	public function run($query = '') {
		$this->global['router'] = new Router($this->global);
		
		if (empty($query)) {
			$query = $_GET['q'];
		}
		// some init-ing
		$this->global['sitenode'] = $this->get_sitenode();
				
		// TODO: change this to configable in acp
		if (empty($query)) {
			$query = 'node/show/' . $this->global['sitenode']->node_id;
		}
		
		
		$this->global['router']->route($query);
		// TODO: create selection
		$this->global['style'] = 'default';
		$parts = $this->global['router']->parts;
		$action = (!empty($parts['action'])) ? $parts['action'] : 'main';

		// get the layout
		$layout = $this->get_controller('layout');
		$layout->view = new View($this->global);
		$layout->view->path = 'style/page.php';
		$this->global['layout'] = $layout;
		
		$controller = $this->get_controller($parts['controller']);
		$controller->arguments = explode('/', $parts['params']);
		$controller->view = new View($this->global);
		$controller->$action();
		$content = $controller->view->display();
		
		// create layout
		$layout->page($content);
		echo $layout->view->display();
	}
	
	/**
	 * Retrieves the site node of this web site.
	 *
	 * @return Node site node
	 */
	public function get_sitenode() {
		// create a temporary node to serve as the main root
		$node = new Node();
		$node->id = 0;
		$sites = $node->get_children();
		
		// now check the hostname
		foreach ($sites as $node) {
			if ($node->options['hostname'] == '' && !isset($default)) {
				// save it for the default
				$default = $node;
			} else if ($_SERVER['HTTP_HOST'] == $node->options['hostname']) {
				return $node; // return immediately
			}
		}
		
		return $default;
	}
	
	/**
	 * Loads the controller with name $name.
	 *
	 * @param string $name
	 * @return Controller the controller
	 */
	public function get_controller($name) {
		$name = strtolower($name);
	
		$files = array(
			'controllers/' . $name . '.php'
		);
		
		foreach (self::$extpaths as $extension => $path) {
			$files[] = dirname($path) . '/controllers/' . $name . '.php';
		}
		
		include_once(self::scan_files($files));
		$class_name = ucfirst(strtolower($name)) . 'Controller';
		
		return new $class_name($this->global);
	}

	/**
	 * Show a 404 page.
	 *
	 */
	public function page_not_found() {
		if (!isset($this->global['404_done']) && isset($this->global['sitenode']->options['404_url'])) {
			$this->global['404_done'] = true;
			$this->run($this->global['sitenode']->options['404_url']);
			exit;
			/*$controller = $this->get_controller('node');
			$controller->view = new View($this->global);
			$this->global['router']->parts['controller'] = 'node';
			$this->global['router']->parts['action'] = 'show';
			$controller->view->reset_path();
			$controller->arguments = array($this->global['sitenode']->options['404_node']);
			$controller->show();
			$content = $controller->view->display();*/
		}
		
		$this->global['layout']->view['title'] = __('Page not found');
		$content = __('This page could not be found.');			
		$this->global['layout']->page($content);
		echo $this->global['layout']->view->display();
		exit;
	}
	
	static function array_merge_keys($arr1, $arr2) {
	    foreach ($arr2 as $k=>$v) {
	        if (!array_key_exists($k, $arr1)) {
	            $arr1[$k] = $v;
	        }
	        else {
	            if (is_array($v)) {
	                $arr1[$k] = self::array_merge_keys($arr1[$k], $arr2[$k]);
	            }
	        }
	    }
	    return $arr1;
	}
	
	/**
	 * Runs a hook on all extensions.
	 *
	 * @example
	 * <code>
	 * manager::run_hook_all('hook', 'parameter', true);
	 * </code>
	 * @return mixed hook results
	 */
	static function run_hook_all() {
		$args = func_get_args();
		$hook_name = array_shift($args);
		$return = array();
		$extensions = self::load_all_extensions();
		 
		foreach ($extensions as $ext) {
			if (method_exists($ext, $hook_name)) {
				$result = call_user_func_array(array($ext, $hook_name), $args);
			    if (isset($result) && is_array($result)) {
					//$return = array_merge($return, $result);
					$return = self::array_merge_keys($return, $result);
			    } else if (isset($result)) {
					$return[] = $result;
				}
			}
		}
		return $return;
	}
	
	/**
	 * Loads all extensions.
	 *
	 * @return array with extension objects
	 */
	static function load_all_extensions() {
		$return = array();
		
		foreach (self::$extpaths as $name => $dummy) {
			$return[] = self::load_extension($name);
		}
		
		return $return;
	}
	
	/**
	 * Loads a specific extension
	 *
	 * @param string $name
	 * @return extension object
	 */
	static function load_extension($name) {
		include_once(self::$extpaths[$name]);
		$classname = 'extension_' . $name;
		
		if (!class_exists($classname)) {
			throw new Exception('This extension does not exist!');
		}
		
		return new $classname($this->global);
	}

	static function handle_error($errno, $msg_text, $errfile, $errline)
	{
		global $msg_title, $msg_long_text;
	
		// Message handler is stripping text. In case we need it, we are possible to define long text...
		if (isset($msg_long_text) && $msg_long_text && !$msg_text)
		{
			$msg_text = $msg_long_text;
		}
		if(!function_exists('__'))
		{
			function __($name)
			{
				return $name;
			}
		}
		$msg_text = __($msg_text);
		
		switch ($errno)
		{
			case E_NOTICE:
			case E_WARNING:
	
				// Check the error reporting level and return if the error level does not match
				// Additionally do not display notices if we suppress them via @
				// If DEBUG is defined the default level is E_ALL
				if (($errno & error_reporting()) == 0)
				{
					return;
				}
	
				// remove complete path to installation, with the risk of changing backslashes meant to be there
				$errfile = str_replace(array(ROOT_PATH, '\\'), array('', '/'), $errfile);
				$msg_text = str_replace(array(ROOT_PATH, '\\'), array('', '/'), $msg_text);

				echo '<strong>[viennaCMS] PHP Notice</strong>: in file <b>' . $errfile . '</b> on line <b>' . $errline . '</b>: <b>' . $msg_text . '</b><br />' . "\n";
				return;
	
			break;
	
			case E_USER_ERROR:
	
				$msg_title = __('General Error');
	
				$path = (defined('IN_INSTALL')) ? '../' : '';
				
				$error_type = strtolower($msg_title);
				$error_text = <<<HTML
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>$msg_title</title>
		<link rel="stylesheet" href="{$path}styles/default/style.css" />
	</head>
	<body>
	<div id="wrap">
	<div id="header">
	</div>
	<div id="content">
		<span class="breadcrumbs"></span>
		<h1 id="pagetitle">$msg_title</h1>
		<br style="clear: both;" />
		<div>While loading this page, a $error_type occured on line <strong>$errline</strong> in <strong>$errfile</strong>:<br />$msg_text</div>
	</div>
	<div id="footer">
		Powered by <a href="http://viennacms.nl/">viennaCMS</a>
	</div>
	</div>	
			</body>
</html>				
HTML;
				
				echo $error_text;
				exit;
			break;
			
			case E_USER_WARNING:
			case E_USER_NOTICE:
				exit;
			break;
		}
	
		// If we notice an error not handled here we pass this back to PHP by returning false
		// This may not work for all php versions
		return false;
	}
	
	/**
	 * Searches for a specific file in an array.
	 *
	 * @param array $array
	 * @return string fiilename
	 */
	static public function scan_files($array) {
		foreach ($array as $file) {
			if (file_exists(ROOT_PATH . $file)) {
				// TODO: cache the result
				return ROOT_PATH . $file;
			}
		}
		
		throw new Exception('Could not find files.');
	}
}

// placeholder
function __($msg) {
	return $msg;
}