<?php
/**
* viennaCMS2 initialization file
* 
* @package framework
* @copyright (c) 2008 viennaCMS group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

$base_memory_usage = memory_get_usage();

define('VIENNACMS_PATH', dirname(dirname(__FILE__)) . '/');

if (isset($_GET['vEIMG'])) {
	include(VIENNACMS_PATH . 'blueprint/errordata.php');

	header('Content-type: image/png');
	echo base64_decode($images[$_GET['vEIMG']]);
	exit;
}

// FIXME: kill this function
function __autoload($class_name) {
	// initial autoload function for initialisation
	if ($class_name == 'VAuth') { // TODO: fix this stuff
		$class_name = 'Auth';
	}
	
	if ($class_name == 'VEvents') { // TODO: really fix this stuff
		$class_name = 'Events';
	}
	
	if ($class_name == 'VObject') {
		$class_name = 'Object';
	}
	
	$filename = VIENNACMS_PATH . 'framework/classes/' . strtolower($class_name) . '.php';
	
	if (file_exists($filename)) {
		include_once($filename);
		return true;
	}
	
	if ($class_name == 'VUser') { // and that's a strange hack
		$class_name = 'User';
	}
	
	if ($class_name == 'VSession') { // wow
		$class_name = 'Session';
	}
	
	$filename = VIENNACMS_PATH . 'framework/models/' . strtolower($class_name) . '.php';
	
	if (file_exists($filename)) {
		include_once($filename);
		return true;
	}
}

function cleanup() {
	if (!empty(cms::$user)) {
		cms::$user->exit_clean();
		
		if ((string)cms::$config['last_session_cleanup'] <= (time() - (3600 * 6))) {
			cms::$user->cleanup();
			
			cms::$config['last_session_cleanup'] = time();
		}
	}
	
	if (!empty(cms::$cache)) {
		cms::$cache->unload();
	}
	
	if (!empty(cms::$db)) {
		cms::$db->sql_close();
	}
}

spl_autoload_register('__autoload');
set_error_handler(array('Manager', 'handle_error'));
set_exception_handler(array('cms', 'handle_exception'));

register_shutdown_function('cleanup');

if (version_compare(phpversion(), '6.0.0-dev', '<') && get_magic_quotes_gpc()) {
	define('STRIP', true);
} else {
	define('STRIP', false);
}

if (STRIP) {
	if( is_array($_GET) )
	{
		foreach ($_GET as $k => $v)
		{
			if( is_array($_GET[$k]) )
			{
				foreach ($_GET[$k] as $k2 => $v2)
				{
					$_GET[$k][$k2] = stripslashes($v2);
				}
			}
			else
			{
				$_GET[$k] = stripslashes($v);
			}
		}
	}
	
	if( is_array($_POST) )
	{
		Foreach ($_POST as $k => $v)
		{
			if( is_array($_POST[$k]) )
			{
				foreach ($_POST[$k] as $k2 => $v2)
				{
					$_POST[$k][$k2] = stripslashes($v2);
				}
				@reset($_POST[$k]);
			}
			else
			{
				$_POST[$k] = stripslashes($v);
			}
		}
		@reset($_POST);
	}
	
	if( is_array($_COOKIE) )
	{
		foreach ($_COOKIE as $k => $v)
		{
			if( is_array($_COOKIE[$k]) )
			{
				foreach ($_COOKIE[$k] as $k2 => $v2)
				{
					$_COOKIE[$k][$k2] = stripslashes($v2);
				}
				@reset($_COOKIE[$k]);
			}
			else
			{
				$_COOKIE[$k] = stripslashes($v);
			}
		}
		@reset($_COOKIE);
	}
}

$mtime = explode(' ', microtime());

include(VIENNACMS_PATH . 'framework/classes/types.php');

cms::$vars = new GlobalStore();
cms::$vars['starttime'] = $mtime[0] + $mtime[1];
cms::$vars['base_memory_usage'] = $base_memory_usage;
unset($base_memory_usage);

VEvents::register('core.autoload-class-name', array('cms', 'class_alterations'));

cms::register('registry');
spl_autoload_unregister('__autoload');
spl_autoload_register(array(cms::$registry, 'autoload'));
cms::$registry->register_loader('framework/classes');
cms::$registry->register_loader('framework/models');
cms::$registry->register_loader('framework/controllers', 'controller');
cms::$registry->register_loader('blueprint/classes');
cms::$registry->register_loader('blueprint/models');
cms::$registry->register_loader('blueprint/controllers', 'controller');
cms::$registry->register_loader('blueprint/nodes', 'node');

$dbms = '';
$table_prefix = 'viennacms_';
$dbhost = '';
$dbuser = '';
$dbpasswd = '';
$dbname = '';
$acm_type = 'acm_file';

if (file_exists('config.php')) {
	@include(VIENNACMS_PATH . 'config.php');
}

// initial language coding
// won't do anything to developers debugging :)

cms::$vars['gettext'] = new VGetText();

if (!defined('DEBUG')) {
	cms::$vars['gettext']->add_searchfolder('locale');
	
	$languages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
	
	foreach ($languages as $language) {
		$lang = substr($language, 0, 2);
		
		if (cms::$vars['gettext']->load_language($lang)) {
			break;	
		}
	}
}

if (!file_exists('config.php') || empty($dbms)) {
	define('MINIMAL', true);
	
	$manager = new Manager();
	$manager->run('install/fresh');
	exit;
}

include(VIENNACMS_PATH . 'framework/database/' . $dbms . '.php');

cms::$vars['table_prefix'] = $table_prefix;
cms::register('db', 'database');
cms::$db->sql_connect($dbhost, $dbuser, $dbpasswd, $dbname);
cms::register('cache', $acm_type);

//try {
//	$global['db'] = newADOConnection($dbms);
//	$global['db']->connect($dbhost, $dbuser, $dbpasswd, $dbname);
//} catch (ADODB_Exception $e) {
//	throw new ViennaCMSException('Could not connect to the database at this moment.');
//}

unset($dbpasswd);

// initialize initial required stuff. not too much, or the code will bomb out.
//spl_autoload_register(array('cms', 'autoload')); // base blueprint models
cms::register('config');
View::$searchpaths['blueprint/views/'] = VIEW_PRIORITY_STOCK; // we need admin/simple.php further along

cms::check_upgrade();

cms::register('user', 'Users');
cms::$user->initialize();

cms::register('files');
cms::$files->init();
cms::register('helpers');
cms::$helpers->init_trash();

// FIXME: enable when ICE is done.
/*if (!isset(cms::$config['ice_created_user'])) {
	$user = new VUser();
	$user->user_id = 1;
	$users = $user->read();
	
	if (count($users) == 1) {
		cms::$config['ice_created_user'] = time();
	} else {
		$manager = new Manager();
		$manager->run('install/ice');
		exit;
	}
}*/
