<?php
/**
* viennaCMS2 initialization file
* 
* @package framework
* @copyright (c) 2008 viennaCMS group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

$base_memory_usage = memory_get_usage();

define('ROOT_PATH', dirname(dirname(__FILE__)) . '/');

function __autoload($class_name) {
	// initial autoload function for initialisation
	
	$filename = ROOT_PATH . 'framework/classes/' . strtolower($class_name) . '.php';
	
	if (file_exists($filename)) {
		include_once($filename);
		return true;
	}
	
	$filename = ROOT_PATH . 'framework/models/' . strtolower($class_name) . '.php';
	
	if (file_exists($filename)) {
		include_once($filename);
		return true;
	}
}

function cleanup() {
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
		while( list($k, $v) = each($_GET) )
		{
			if( is_array($_GET[$k]) )
			{
				while( list($k2, $v2) = each($_GET[$k]) )
				{
					$_GET[$k][$k2] = stripslashes($v2);
				}
				@reset($_GET[$k]);
			}
			else
			{
				$_GET[$k] = stripslashes($v);
			}
		}
		@reset($_GET);
	}
	
	if( is_array($_POST) )
	{
		while( list($k, $v) = each($_POST) )
		{
			if( is_array($_POST[$k]) )
			{
				while( list($k2, $v2) = each($_POST[$k]) )
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
		while( list($k, $v) = each($_COOKIE) )
		{
			if( is_array($_COOKIE[$k]) )
			{
				while( list($k2, $v2) = each($_COOKIE[$k]) )
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

cms::$vars = new GlobalStore();
cms::$vars['starttime'] = $mtime[0] + $mtime[1];
cms::$vars['base_memory_usage'] = $base_memory_usage;
unset($base_memory_usage);
//$global = cms::$vars;

//include(ROOT_PATH . 'framework/db/adodb-exceptions.inc.php');
//include(ROOT_PATH . 'framework/db/adodb.inc.php');
//include(ROOT_PATH . 'framework/db/adodb-active-record.inc.php');
@include(ROOT_PATH . 'framework/config/basic.php');

if (empty($dbms)) {
	define('MINIMAL', true);
	
	$manager = new Manager();
	$manager->run('install/fresh');
	exit;
}

include(ROOT_PATH . 'framework/database/' . $dbms . '.php');

if (empty($acm_type)) {
	$acm_type = 'acm_file';
}

cms::$vars['table_prefix'] = $table_prefix;
cms::register('db', new database());
cms::$db->sql_connect($dbhost, $dbuser, $dbpasswd, $dbname);
cms::register('cache', new $acm_type());

//try {
//	$global['db'] = newADOConnection($dbms);
//	$global['db']->connect($dbhost, $dbuser, $dbpasswd, $dbname);
//} catch (ADODB_Exception $e) {
//	throw new ViennaCMSException('Could not connect to the database at this moment.');
//}

unset($dbpasswd);

//ADOdb_Active_Record::SetDatabaseAdapter($global['db']);

//var_dump(Node::create('Node', $global));

/*
$node = new Node($global);
$node->node_id = 2;
$node->type = 'page';
$node->read(true);
$node->options['wef'] = 'aef';
$node->write();
*/

/*
$node = Node::create('Node', $global);
$node->parent = 1;
$node->title = 'Barks';
$node->description = 'Woof';
$node->type = 'page';
$node->options['wef'] = 'aef';
$node->write();
echo $node->node_id;
var_dump($global['db']->num_queries);
*/

cms::register('user', new Users());
cms::$user->initialize();

// add other auto-loading classes
spl_autoload_register(array('cms', 'autoload'));
spl_autoload_register(array('controller', 'autoload'));
Controller::$searchpaths[] = 'blueprint/controllers/';
View::$searchpaths['blueprint/views/'] = VIEW_PRIORITY_STOCK;
