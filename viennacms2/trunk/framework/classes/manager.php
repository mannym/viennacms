<?php

class Manager {
	private $global;
	
	public function __construct($global) {
		$this->global = $global;
	}
	
	public function run() {
		$this->global['router'] = new Router($this->global);
		
		$query = $_GET['q'];
		
		// TODO: change this
		if (empty($query)) {
			$query = 'index/main';
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
	
	public function get_controller($name) {
		include(ROOT_PATH . 'controllers/' . strtolower($name) . '.php');
		$class_name = ucfirst(strtolower($name)) . 'Controller';
		
		return new $class_name($this->global);
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