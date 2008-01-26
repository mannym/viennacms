<?php
/**
* Utilities class for viennaCMS.
* 
* @package viennaCMS
* @author viennainfo.nl
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

	/**
	* gets types if not yet fetched
	*/
	
	static function get_types() {
		if (!empty(self::$types) || !is_array(self::$types)) {
			self::$types = self::run_hook_all('list_types');
		}
	}
	
	/**
	* Connects to the database. Is automatically called by the database class on first
	* getnew(). 
	*/
	
	static function connect_db() {
		if (defined('IN_INSTALL')) {
			return;
		}
		
		$db = database::getnew(); // need some error checking so using this when
								  // the database class is not loaded we don't loop :)
		@include(ROOT_PATH . 'config.php');
		
		if (!defined('CMS_INSTALLED')) {
			header('Location: install/');
			exit;
			//exit(__('You need to install viennaCMS first.'));
		}
		
		$db->sql_connect($dbhost, $dbuser, $dbpasswd, $dbname);
		$db->prefix = $table_prefix;
		
		include(ROOT_PATH . 'includes/constants_core.php');
	}
	
	/**
	 * Check if the email address is valid.
	 */
	
	function validate_email($add) {
		$regex = "#^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+)\.+([a-zA-Z0-9]{2,4})+$#i";
		if(!preg_match($regex, $add, $match)) {
			return false;
		}
		/**
		* Removed because this make the function to slow
		*/
		/*$addr = 'http://' . $match[2] . '.' . $match[4];
		$addr2 = 'https://' . $match[2] . '.' . $match[4];
		if(!fopen($addr, 'r') && !fopen($addr2, 'r')) {
			return false;
		}*/
		return true;
	}
	
	/**
	* Adds CSS to the header.
	*/
	
	static function add_css($type = 'file', $data) {
		global $Header;
	
		switch ($type) {
			case 'file':
				$Header .= '<link rel="stylesheet" href="' . $data . '" type="text/css" />' . "\n";
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
				$Header .= '<script type="text/javascript" src="' . $data . '"></script>' . "\n";
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
	
	static function node_select($name) {
		$node = new CMS_Node();
		$node->node_id = 0;
		$text = '<ul class="nodes">';
		$text .= self::_get_select_tree($node, '', $name);
		$text .= '</ul>';
		$text .= '<input type="hidden" name="' . $name . '" id="' . $name . '" />';
		
		return $text;
	}
	
	static function handle_text($text) {
		$text = urldecode($text);
		$page = page::getnew();
		$text = preg_replace_callback('@href="\{node\:([0-9]+)\}"@', array(&$page, 'get_correct_link'), $text);
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
		return $url;		
	}
	
	static function basepath() {
		$url = dirname($_SERVER['SCRIPT_NAME']);
		if (dirname($_SERVER['SCRIPT_NAME']) != '/') {
			$url .= '/';
		}		
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
	
	static function run_hook_all() {
		$args = func_get_args();
		$hook_name = array_shift($args);
		$return = array();
		$extensions = self::load_all_exts();
		foreach ($extensions as $ext) {
			if (method_exists($ext, $hook_name)) {
				$result = call_user_func_array(array($ext, $hook_name), $args);
			    if (isset($result) && is_array($result)) {
					$return = array_merge($return, $result);
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

	function handle_error($errno, $msg_text, $errfile, $errline)
	{
		global $msg_title, $msg_long_text;
	
		// Message handler is stripping text. In case we need it, we are possible to define long text...
		if (isset($msg_long_text) && $msg_long_text && !$msg_text)
		{
			$msg_text = $msg_long_text;
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
				$l_notify = '';

				// $l_notify = '<p>' . __('Please notify the site administrator or webmaster: ') . ' <a href="mailto:' . $config->get('admin_contact') . '">' . $config->get('admin_contact') . '</a></p>';
	
				// Try to not call the adm page data...
	
				echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
				echo '<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">';
				echo '<head>';
				echo '<meta http-equiv="content-type" content="text/html; charset=utf-8" />';
				echo '<title>' . $msg_title . '</title>';
				echo '<style type="text/css">' . "\n" . '<!--' . "\n";
				echo '* { margin: 0; padding: 0; } html { font-size: 100%; height: 100%; margin-bottom: 1px; background-color: #E4EDF0; } body { font-family: "Lucida Grande", Verdana, Helvetica, Arial, sans-serif; color: #536482; background: #E4EDF0; font-size: 62.5%; margin: 0; } ';
				echo 'a:link, a:active, a:visited { color: #006699; text-decoration: none; } a:hover { color: #DD6900; text-decoration: underline; } ';
				echo '#wrap { padding: 0 20px 15px 20px; min-width: 615px; } #page-header { text-align: right; height: 40px; } #page-footer { clear: both; font-size: 1em; text-align: center; } ';
				echo '.panel { margin: 4px 0; background-color: #FFFFFF; border: solid 1px  #A9B8C2; } ';
				echo '#errorpage #page-header a { font-weight: bold; line-height: 6em; } #errorpage #content { padding: 10px; } #errorpage #content h1 { line-height: 1.2em; margin-bottom: 0; color: #DF075C; } ';
				echo '#errorpage #content div { margin-top: 20px; margin-bottom: 5px; border-bottom: 1px solid #CCCCCC; padding-bottom: 5px; color: #333333; font: bold 1.2em "Lucida Grande", Arial, Helvetica, sans-serif; text-decoration: none; line-height: 120%; text-align: left; } ';
				echo "\n" . '//-->' . "\n";
				echo '</style>';
				echo '</head>';
				echo '<body id="errorpage">';
				echo '<div id="wrap">';
				echo '	<div id="page-header">';
				echo '	</div>';
				echo '	<div id="acp">';
				echo '	<div class="panel">';
				echo '		<div id="content">';
				echo '			<h1>' . $msg_title . '</h1>';
				
				echo '			<div>' . $msg_text . '</div>';
				
				echo $l_notify;
	
				echo '		</div>';
				echo '	</div>';
				echo '	</div>';
				echo '</div>';
				echo '</body>';
				echo '</html>';
				
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
	
	static function _get_admin_tree($node, $list = '') {
		self::get_types();
		
		$ext = self::load_extension(self::$types[$node->type]['extension']);
		$show = true;
		if (method_exists($ext, $node->type . '_in_tree')) {
			$function = $node->type . '_in_tree';
			$show = $ext->$function($node);
		}
		
		if ($show) {
			if ($node->node_id != 0) {
				$list .= '<li><a href="admin_node.php?node=' . $node->node_id . '" class="' . $node->type . '">' . $node->title . '</a>' . "\r\n";			
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

	static function _get_select_tree($node, $list = '', $name = '') {
		if ($node->node_id != 0) {
			$list .= '<li id="' . $name . '-' . $node->node_id . '"><a href="javascript:void()" onclick="select_node(\'' . $name . '\', ' . $node->node_id . '); return false;" class="' . $node->type . '">' . $node->title . '</a>' . "\r\n";			
		}
		
		$nodes = $node->get_children();
		
		if ($nodes) {
			$list .= '<ul>';
			foreach ($nodes as $node) {
				$list = self::_get_select_tree($node, $list, $name);
			}
			$list .= '</ul>';
		}
		
		$list .= '</li>';
		return $list;
	}
	
	static function write_config($dbhost, $dbuser, $dbpasswd, $dbname, $prefix) {
		$config = <<<CONFIG
<?php
\$dbhost = '$dbhost';
\$dbuser = '$dbuser';
\$dbpasswd = '$dbpasswd';
\$dbname = '$dbname';

\$table_prefix = '$prefix';

define('CMS_INSTALLED', true);
?>
CONFIG;
		file_put_contents(ROOT_PATH . 'config.php', $config);
	}
}
?>