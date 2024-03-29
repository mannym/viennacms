<?php
/**
* Utilities class for viennaCMS.
* 
* @package viennaCMS
* @author viennacms.nl
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

/**
* @ignore
*/

if (!defined('IN_VIENNACMS')) {
	exit;
}

/**
* The one and only utils class!
* 
* @package viennaCMS
*/

class utils {
	static public $types;
	static public $extensions;

	static function lsort_callback($a, $b) {
		return (strlen($b) - strlen($a));
		
	}
	
	/**
	* gets types if not yet fetched
	*/
	
	static function get_types() {
		if (!empty(self::$types) || !is_array(self::$types)) {
			self::$types = self::run_hook_all('list_types');
		}
	}
	
	/**
	* gets the current database version
	*/
	
	static function get_database_version() {
		$db = database::getnew();
		include(ROOT_PATH . 'includes/version.php');
		
		$db->sql_return_on_error(true); // We need this so we don't get an error message
										// when files are 1.x, and db is 0.9.x.
		$sql = 'SELECT * FROM ' . CONFIG_TABLE . " WHERE config_name = 'database_version'";
		$result = $db->sql_query($sql);
		
		if ($result !== false) {
			if (!$row = $db->sql_fetchrow($result)) {
				$currentdbver = 109;
			} else {
				$currentdbver = $row['config_value'];
			}
		} else {
			$sql = 'SELECT * FROM ' . NODE_OPTIONS_TABLE . " WHERE node_id = 0 AND option_name = 'database_version'";
			$result = $db->sql_query($sql);
			if ($row = $db->sql_fetchrow($result)) {
				$currentdbver = $row['option_value'];
			} else {
				$currentdbver = 0;
			}
		}
		$return = array(
			'current' => $currentdbver,
			'new' => $database_version,
			'uptodate' => (version_compare($currentdbver, $database_version, '>=')),
		);
		
		$db->sql_return_on_error(false); // but now it has to be false again :)
		
		return $return;
	}
	
	/**
	 * placeholder
	 */
	 
	function url($link, $options = array()) {
		$return = self::base();
		if (!isset($options['nonsys_url'])) {
			$page = page::getnew(false);
			if (!empty($page->sitenode) && !$page->sitenode->options['rewrite']) {
				$return .= 'index.php/';
			}	
		}
		$return .= $link;
		return $return;
	}
	
	/**
	* Connects to the database. Is automatically called by the database class on first
	* getnew(). 
	*/
	
	static function connect_db() {
		if (defined('IN_INSTALL')) {
			return;
		}
		global $dbhost, $dbuser, $dbpasswd, $dbname, $table_prefix;
		$db = database::getnew();
		
		@include_once(ROOT_PATH . 'config.php');

		$db->sql_connect($dbhost, $dbuser, $dbpasswd, $dbname);
		$db->prefix = $table_prefix;

		include(ROOT_PATH . 'includes/constants_core.php');
	}
	
	/**
	 * Check if the email address is valid.
	 */
	
	function validate_email($add) {
		$regex = "#^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+)\.+([a-zA-Z0-9]{2,4})+$#i";
		if(!preg_match($regex, $add)) {
			return false;
		}

		return true;
	}
	
	/**
	* Adds CSS to the header.
	*/
	
	static function add_css($type = 'file', $data) {
		global $Header;
	
		switch ($type) {
			case 'file':
				$Header .= '<link rel="stylesheet" href="' . utils::url($data, array('nonsys_url' => true)) . '" type="text/css" />' . "\n";
			break;
			case 'inline':
				$Header .= '<style type="text/css">' . "\r\n";
				$Header .= $data;
				$Header .= "\r\n" . '</style>' . "\r\n";
			break;
		}
	}

	/**
	* Adds JavaScript to the header.
	*/
	
	static function add_js($type = 'file', $data) {
		global $Header;
	
		switch ($type) {
			case 'file':
				$Header .= '<script type="text/javascript" src="' . utils::url($data, array('nonsys_url' => true)) . '"></script>' . "\n";
			break;
			case 'inline':
				$Header .= '<script type="text/javascript">' . "\r\n";
				$Header .= $data;
				$Header .= "\r\n" . '</script>' . "\r\n";
			break;
		}
	}
	
	/**
	* Generates a node selector.
	*/
	
	static function node_select($name, $callback = false, $cbtype = 0) {
		$node = new CMS_Node();
		$node->node_id = 0;
		$text = '<ul class="nodes">';
		$text .= self::_get_select_tree($node, '', $name, $callback, $cbtype);
		$text .= '</ul>';
		$text .= '<input type="hidden" name="' . $name . '" id="' . $name . '" />';
		
		return $text;
	}
	
	static function handle_text($text) {
		$text = urldecode($text);
		$page = page::getnew();
		$files = self::load_extension('files');
		$text = preg_replace_callback('@href=".*?\{node\:([0-9]+)\}"@', array(&$page, 'get_correct_link'), $text);
		$text = preg_replace_callback('@{viennafile\:([0-9]+)\}@', array(&$files, 'get_widget'), $text);
		return $text;
	}
	
	/**
	* Removes a selected value from an array. 
	*/

	static function remove_array($source, $value) {
		$new = array();
		
		foreach ($source as $key => $kvalue) {
			if ($value != $kvalue) {
				$new[$key] = $kvalue;
			}
		}
		
		return $new;
	}
	
	static function clean_title($title) {
		$title = strip_tags($title);
		// Preserve escaped octets.
		$title = preg_replace('|%([a-fA-F0-9][a-fA-F0-9])|', '---$1---', $title);
		// Remove percent signs that are not part of an octet.
		$title = str_replace('%', '', $title);
		// Restore octets.
		$title = preg_replace('|---([a-fA-F0-9][a-fA-F0-9])---|', '%$1', $title);
	
		$title = self::remove_accents($title);
	
		$title = strtolower($title);
		$title = preg_replace('/&.+?;/', '', $title); // kill entities
		$title = preg_replace('/[^%a-z0-9 _-]/', '', $title);
		$title = preg_replace('/\s+/', '-', $title);
		$title = preg_replace('|-+|', '-', $title);
		
		$title = trim($title, '-');
	
		return $title;
	}
	
	static function base() {
		$url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https' : 'http';
		$url .= '://' . $_SERVER['HTTP_HOST'];
		$url .= dirname($_SERVER['SCRIPT_NAME']);
		if (dirname($_SERVER['SCRIPT_NAME']) != '/') {
			$url .= '/';
		}
		$url = str_replace('/adm', '', $url);
		return $url;		
	}
	
	static function basepath() {
		$url = dirname($_SERVER['SCRIPT_NAME']);
		if (dirname($_SERVER['SCRIPT_NAME']) != '/') {
			$url .= '/';
		}	
		$url = str_replace(array('/adm', '/install'), '', $url);	
		return $url;		
	}
	
	static function remove_accents($string) {
		if ( !preg_match('/[\x80-\xff]/', $string) )
			return $string;
	
		// Assume ISO-8859-1 if not UTF-8
		$chars['in'] = chr(128).chr(131).chr(138).chr(142).chr(154).chr(158)
			.chr(159).chr(162).chr(165).chr(181).chr(192).chr(193).chr(194)
			.chr(195).chr(196).chr(197).chr(199).chr(200).chr(201).chr(202)
			.chr(203).chr(204).chr(205).chr(206).chr(207).chr(209).chr(210)
			.chr(211).chr(212).chr(213).chr(214).chr(216).chr(217).chr(218)
			.chr(219).chr(220).chr(221).chr(224).chr(225).chr(226).chr(227)
			.chr(228).chr(229).chr(231).chr(232).chr(233).chr(234).chr(235)
			.chr(236).chr(237).chr(238).chr(239).chr(241).chr(242).chr(243)
			.chr(244).chr(245).chr(246).chr(248).chr(249).chr(250).chr(251)
			.chr(252).chr(253).chr(255);
	
		$chars['out'] = "EfSZszYcYuAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy";
	
		$string = strtr($string, $chars['in'], $chars['out']);
		$double_chars['in'] = array(chr(140), chr(156), chr(198), chr(208), chr(222), chr(223), chr(230), chr(240), chr(254));
		$double_chars['out'] = array('OE', 'oe', 'AE', 'DH', 'TH', 'ss', 'ae', 'dh', 'th');
		$string = str_replace($double_chars['in'], $double_chars['out'], $string);
	
		return $string;
	}
	
	static function list_extensions() {
		$return = array();
		$files = scandir(ROOT_PATH . 'extensions/');
		foreach ($files as $file) {
			$rfile = ROOT_PATH . 'extensions/' . $file;
			if ($file != '.' && $file != '..' && is_dir($rfile) && file_exists($rfile . '/index.php')) {
				if ($file == 'install') {
					continue;
				}
				
				include_once($rfile . '/index.php');
				$class = 'extension_' . $file;
				
				if (!class_exists($class)) {
					continue;
				}
				
				$ext = new $class;
				$info = $ext->extinfo();
				$return[$file] = $info;
			}
		}
		return $return;
	}
	
	static function load_extension($extension) {
		if (isset(self::$extensions[$extension])) {
			return self::$extensions[$extension];
		}
	
		@include_once(ROOT_PATH . 'extensions/' . $extension . '/index.php');
		$class = 'extension_' . $extension;
		
		if (!class_exists($class)) {
			return;
		}
		
		$ext = new $class;
		self::$extensions[$extension] = $ext;
		return $ext;
	}
	
	static function load_all_exts() {
		$extensions = self::list_extensions();
		$return = array();
		foreach ($extensions as $key => $dummy) {
			$return[$key] = self::load_extension($key); 
		}
		return $return;
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
	
	static function run_hook_all() {
		$args = func_get_args();
		$hook_name = array_shift($args);
		$return = array();
		if (defined('IN_INSTALL') && strlen($hook_name) >= 6 && substr_compare($hook_name, 'admin_', 0, 6) !== false) {
			$extensions = array(self::load_extension('install'));
		} else {
			$extensions = self::load_all_exts();
		}
		 
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
	* Handles errors thrown by PHP, or trigger_error. 
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
		Powered by <a href="http://viennainfo.nl/">viennaCMS</a>
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
				if (defined('IN_ADMIN')) {
					$page_title = __('Information');
					echo <<<HTML
<html>
	<head>
		<title><$page_title</title>
		<link rel="stylesheet" href="style/style.css" />
	</head>
	<body>
<div id="main-items">
</div>
<div id="tree-left">
</div>
<div id="system-right">
HTML;
					echo '<h1>' . __('Information') . "</h1>\r\n<div>";
					echo $msg_text . '<br /><br /></div>';
					echo <<<HTML
		</div>
	</body>
</html>
HTML;
					exit;
				}
				
				$template = template::getnew();
				$page = page::getnew(false);
				$template->set_filename('error', 'index.php');
				$template->assign_vars(array(
					'sitename'	=> (isset($page->sitenode->title)) ? $page->sitenode->title : 'viennaCMS',
					'title' 	=> __('Information'),
					'content'	=> $msg_text,
					'middle'	=> $msg_text,
					'head'		=> '<base href="' . self::base() . '" />'
				));
				$template->display('error');
				exit;
			break;
		}
	
		// If we notice an error not handled here we pass this back to PHP by returning false
		// This may not work for all php versions
		return false;
	}

	static function get_admin_tree() {
		$node = new CMS_Node();
		$node->node_id = 0;
		echo self::_get_admin_tree($node);
	}
	
	static function display_allowed($type, $node, $other = false) {
		$result = utils::run_hook_all('display_node', $type, $node, $other);
		$return = true;
		foreach ($result as $res) {
			if ($res == false) {
				$return = false;
				break;
			}
		}
		
		if ($return) {
			if ($type == 'this_under_other') {
				$return = self::display_allowed('other_under_this', $other, $node);
			}
		}
		
		return $return;
	}
	
	static function _get_admin_tree($node, $list = '') {
		self::get_types();
		
		if ($node->node_id != 0) {
			$ext = self::load_extension(self::$types[$node->type]['extension']);
			//$show = true;
			$show = self::display_allowed('in_tree', $node);
			/*if (method_exists($ext, $node->type . '_in_tree')) {
				$function = $node->type . '_in_tree';
				$show = $ext->$function($node);
			}*/
		} else {
			$show = true;
		}
		
		if ($show) {
			if ($node->node_id != 0) {
				$list .= '<li id="node-' . $node->node_id . '"><a href="admin_node.php?node=' . $node->node_id . '" class="' . $node->type . '">' . $node->title . '</a>' . "\r\n";			
			}
			
			$nodes = $node->get_children();
			
			if ($nodes) {
				$list .= '<ul>';
				foreach ($nodes as $node) {
					$list = self::_get_admin_tree($node, $list);
				}
				$list .= '</ul>';
			}
			
			$list .= '</li>';
		}
		return $list;
	}

	static function _get_select_tree($node, $list = '', $name = '', $callback = false, $cbtype = 0) {
		self::get_types();
		$show = true;	
		$link = true;	
		if ($node->node_id != 0) {
			/*$ext = self::load_extension(self::$types[$node->type]['extension']);
			if (method_exists($ext, $node->type . '_in_tree')) {
				$function = $node->type . '_in_tree';
				$show = $ext->$function($node);
			}*/
			$show = self::display_allowed('in_tree', $node);
			
			if ($callback) {
				if ($callback['ntype'] == 'this') {
					$thisnode = $node;
					$othernode = (isset($callback['node'])) ? $callback['node'] : false;
				} else {
					$othernode = $node;
					$thisnode = (isset($callback['node'])) ? $callback['node'] : false;					
				}
				if ($cbtype == 0) {
					//$show = call_user_func($callback, $node);
					$show = self::display_allowed($callback['type'], $thisnode, $othernode);
				} else if ($cbtype == 1) {
					if ($node->type != 'site') {
						$show = self::display_allowed($callback['type'], $thisnode, $othernode);
						//$show = call_user_func($callback, $node);
					} else {
						$link = self::display_allowed($callback['type'], $thisnode, $othernode);
						//$link = call_user_func($callback, $node);
					}
				}
			}
		}
		
		if ($show) {
			if ($node->node_id != 0) {
				$list .= '<li id="' . $name . '-' . $node->node_id . '">';
				if ($link) {
					$list .= '<a href="#" onclick="select_node(\'' . $name . '\', ' . $node->node_id . '); return false;" class="' . $node->type . '">';
				} else {
					$list .= '<a href="#" onclick="return false;" class="' . $node->type . '">';
				}
				$list .= $node->title;
				$list .= '</a>';
				$list .= "\r\n";			
			}
			
			$nodes = $node->get_children();
			
			if ($nodes) {
				$list .= '<ul>';
				foreach ($nodes as $node) {
					$list = self::_get_select_tree($node, $list, $name, $callback, $cbtype);
				}
				$list .= '</ul>';
			}
			
			$list .= '</li>';
		}
		return $list;
	}
	
	static function set_config($key, $val)
	{
		global $config;
		if(isset($config[$key]) && $config[$key] === $val)
		{
			return true;
		}
		$db = database::getnew();
		$key = $db->sql_escape($key);
		$val = $db->sql_escape($val);
		$sql = 'SELECT * FROM ' . CONFIG_TABLE . " WHERE config_name = '$key';";
		$result = $db->sql_fetchrow($db->sql_query($sql));
		if(!$result)
		{
			$sql = 'INSERT INTO ' . CONFIG_TABLE . "
				(config_name, config_value)
				VALUES(
				'$key', '$val');";
		}
		else {
			$sql = "UPDATE " . CONFIG_TABLE . "
				SET config_value = '$val'
				WHERE config_name = '$key';";		
		}
		$result = $db->sql_query($sql);
		if(!$result) 
		{
			return false;
		}
		else {
			$config[$key] = $val;
			return true;
		}
	}
	
	static function config_file_write($dbhost, $dbuser, $dbpasswd, $dbname, $prefix, $dbms) {
		$config = <<<CONFIG
<?php
\$dbhost = '$dbhost';
\$dbuser = '$dbuser';
\$dbpasswd = '$dbpasswd';
\$dbname = '$dbname';
\$dbms = '$dbms';

\$table_prefix = '$prefix';

@define('CMS_INSTALLED', true);
?>
CONFIG;
		return file_put_contents(ROOT_PATH . 'config.php', $config);
	}

	static function array_move_element($array, $value, $direction = 'up') {
	   
	    $temp = array();
	   
	    if(end($array) == $value && $direction == 'down') {
	        return $array;
	    }
	    if(reset($array) == $value && $direction == 'up') {
	        return $array;
	    }

	    while (($array_value = current($array)) !== false) {
	    	$this_key = key($array);
	        if ($array_value == $value) {
	        	if($direction == 'down') {
	                $next_value = next($array);
	                $temp[key($array)] = $next_value;
	                $temp[$this_key] = $array_value;
	            } else {
	                $prev_value = prev($array);
	                $prev_key = key($array);
	                unset($temp[$prev_key]);
	                $temp[$this_key] = $array_value;
	                $temp[$prev_key] = $prev_value;
	                next($array);
	                next($array);
	            }
	            continue;
	        } else {
	            $temp[$this_key] = $array_value;
	        }
	
	        next($array);
	    }
	    
	    $temp2 = array();
	    foreach ($temp as $value) {
	    	$temp2[] = $value;
	    }
	    return $temp2;
	   
	}
	
	static function get_hours($current = 0) {
		return array(
			'1800' => array(
				'title' => __('half an hour'),
				'selected' => ($current == 1800)
			),
			'3600' => array(
				'title' => __('an hour'),
				'selected' => ($current == 3600)
			),
			'7200' => array(
				'title' => __('two hours'),
				'selected' => ($current == 7200)
			),
			'21600' => array(
				'title' => __('six hours'),
				'selected' => ($current == 21600)
			),
			'43200' => array(
				'title' => __('twelve hours'),
				'selected' => ($current == 43200)
			),
			'86400' => array(
				'title' => __('an day'),
				'selected' => ($current == 86400)
			),
			'172800' => array(
				'title' => __('two days'),
				'selected' => ($current == 172800)
			),
		);
	}
}

function shutdown_cleanly() {
	global $cache;
	$cache->save();
	$cache->unload();
	if (class_exists('database')) {
		$db = database::getnew();
		$db->sql_close();
	}
}
?>