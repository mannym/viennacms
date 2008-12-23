<?php
/**
 * The holy manager :D
 * 
 * @package framework
 * @version $Id$
 * @copyright (c) 2008 viennaCMS group
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */


define('CONTROLLER_OK', 0);
define('CONTROLLER_NO_LAYOUT', 1);
define('CONTROLLER_ERROR', -1);

/**
 * Manager
 * The start of... everything.
 *
 * @package framework
 * @access public
 */
class Manager {
	public static $extpaths = array();
	public $check = 0;
	
	/**
	* Constructor of Manager
	*/
	public function __construct() {
		cms::register('manager', $this);
		// TODO: dynamically load this
		self::$extpaths['core'] = 'extensions/core/core.ext.php';
	}
	
	/**
	* Runs the page.
	*
	* @param string $query URL to parse and run, if empty, the current URL is used.
	*/
	public function run($query = '', $check = false) {
		if ($check) {
			$this->check++;
		}
		
		cms::register('router', new Router());
		
		if (empty($query)) {
			$uri_no_qs = explode('?', $_SERVER['REQUEST_URI']);
			$uri_no_qs = $uri_no_qs[0];

			if (strpos($_SERVER['REQUEST_URI'], '.php') === false
				&& $uri_no_qs != manager::basepath(true) && !isset($_GET['q'])) {
				$query = preg_replace('@^' . preg_quote(manager::basepath(), '@') . '@', '', $uri_no_qs);
			} else if (!empty($_SERVER['PATH_INFO'])) {
				$query = substr($_SERVER['PATH_INFO'], 1);
			}
		}
		// some init-ing
		if (!defined('MINIMAL')) {
			cms::$vars['sitenode'] = $this->get_sitenode();
		}
				
		// TODO: change this to configable in acp
		if (empty($query)) {
			$query = 'node';
		}
		
		$query = (string) $query;
		
		cms::$router->route($query);

		// TODO: create selection
		cms::$vars['style'] = 'default';
		$parts = cms::$router->parts;
		$action = (!empty($parts['action'])) ? $parts['action'] : 'main';

		// get the layout
		if (!defined('MINIMAL')) {
			$layout = $this->get_controller('layout');
			$layout->view = new View();
			$layout->view->path = 'style/page.php';
		} else {
			$layout = $this->get_controller('installstyle');
			$layout->view = new View();
			$layout->view->path = 'page.php';
		}
		
		cms::register('layout', $layout);
		
		$controller = $this->get_controller($parts['controller']);
		if (!$controller) {
			return $this->page_not_found();
		}
		
		$controller->arguments = explode('/', $parts['params']);
		$controller->view = new View();
		
		if (!method_exists($controller, $action)) {
			return $this->page_not_found();
		}
		
		$result = $controller->$action();
		
		if ($check || $result === CONTROLLER_ERROR) {
			$this->check--;
			return $result;
		}
		
		$content = $controller->view->display();
		
		// create layout
		$output = '';
		if ($result == CONTROLLER_OK) {
			$layout->page($content);
			$output = $layout->view->display();
		} else if ($result === CONTROLLER_NO_LAYOUT) {
			$output = $content;
		}
		
		if (defined('DEBUG_EXTRA') && isset($_REQUEST['explain'])) {
			cms::$db->sql_report('display');
			exit;
		}
		
		echo $output;
	
		return CONTROLLER_OK;
	}
	
	/**
	* Retrieves the site node of this web site.
	*
	* @return Node site node
	*/
	public function get_sitenode() {
		// create a temporary node to serve as the main root
		$node = new Node();
		$node->node_id = 0;
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
		if (!Controller::load($name)) {
			return false;
		}
		
		if (strpos($name, '/') !== false) {
			$name = str_replace('/', '', $name);
		}
		
		$class_name = ucfirst(strtolower($name)) . 'Controller';
		return new $class_name();
	}

	/**
	* Show a 404 page.
	*/
	public function page_not_found() {
		if ($this->check) {
			return CONTROLLER_ERROR;	
		}

		if (!isset(cms::$vars['404_done']) && isset(cms::$vars['sitenode']->options['404_url'])) {
			cms::$vars['404_done'] = true;
			$this->run(cms::$vars['sitenode']->options['404_url']);
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
		
		cms::$vars['error_title'] = __('Page not found');
		trigger_error(__('The requested page could not be found.'));
	}
	
	/**
	* Manager::array_merge_keys()
	* 
	* @todo move to cms::
	*/
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
	* @todo move to cms::
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
	 * @todo move to cms::
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
		
		Controller::$searchpaths[] = dirname(self::$extpaths[$name]) . '/controllers/';
		View::$searchpaths[dirname(self::$extpaths[$name]) . '/views/'] = VIEW_PRIORITY_STOCK;
		
		return new $classname($this->global);
	}

	/**
	* @todo move to cms::
	*/ 
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
				cms::$layout->view['title'] = cms::$vars['error_title'];
				$content = $msg_text;			
				cms::$layout->page($content);
				echo cms::$layout->view->display();
				exit;
			break;
		}
	
		// If we notice an error not handled here we pass this back to PHP by returning false
		// This may not work for all php versions
		return false;
	}
}

// placeholder
function __($msg) {
	return $msg;
}