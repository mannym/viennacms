<?php
/**
* viennaCMS2 initialization file
* 
* @package framework
* @copyright (c) 2008 viennaCMS group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

define('ROOT_PATH', dirname(dirname(__FILE__)) . '/');

function __autoload($class_name) {
	$filename = ROOT_PATH . 'framework/classes/' . strtolower($class_name) . '.php';
	
	if (file_exists($filename)) {
		include_once($filename);
		return true;
	}
	
	$filename = ROOT_PATH . 'models/' . strtolower($class_name) . '.php';
	
	if (file_exists($filename)) {
		include_once($filename);
		return true;
	}
}

set_error_handler(array('Manager', 'handle_error'));

if (version_compare(phpversion(), '6.0.0-dev', '<') && get_magic_quotes_gpc()) {
	define('STRIP', true);
} else {
	define('STRIP', false);
}

$global = new GlobalStore();

include(ROOT_PATH . 'framework/db/adodb-exceptions.inc.php');
include(ROOT_PATH . 'framework/db/adodb.inc.php');
include(ROOT_PATH . 'framework/db/adodb-active-record.inc.php');
@include(ROOT_PATH . 'framework/config/basic.php');

if (empty($dbms)) {
	die();
}

try {
	$global['db'] = newADOConnection($dbms);
	$global['db']->connect($dbhost, $dbuser, $dbpasswd, $dbname);
} catch (ADODB_Exception $e) {
	throw new ViennaCMSException('Could not connect to the database at this moment.');
}

unset($dbpasswd);

ADOdb_Active_Record::SetDatabaseAdapter($global['db']);