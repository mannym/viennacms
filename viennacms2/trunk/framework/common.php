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

//include(ROOT_PATH . 'framework/db/adodb-exceptions.inc.php');
//include(ROOT_PATH . 'framework/db/adodb.inc.php');
//include(ROOT_PATH . 'framework/db/adodb-active-record.inc.php');
@include(ROOT_PATH . 'framework/config/basic.php');
include(ROOT_PATH . 'framework/database/' . $dbms . '.php');

if (empty($dbms)) {
	die('You should install viennaCMS2 first.');
}

$global['db'] = new database();
$global['db']->sql_connect($dbhost, $dbuser, $dbpasswd, $dbname);

//try {
//	$global['db'] = newADOConnection($dbms);
//	$global['db']->connect($dbhost, $dbuser, $dbpasswd, $dbname);
//} catch (ADODB_Exception $e) {
//	throw new ViennaCMSException('Could not connect to the database at this moment.');
//}

unset($dbpasswd);

//ADOdb_Active_Record::SetDatabaseAdapter($global['db']);

class Node extends Model {
	protected $table = 'nodes';
	protected $fields = array(
		'node_id' => array('type' => 'int'),
		'title' => array('type' => 'string'),
		'description' => array('type' => 'string'),
		'type' => array('type' => 'string'),
		'parent' => array('type' => 'int'),
		'revision_num' => array('type' => 'int', 'relation' => 'node_to_revision'),
		'created' => array('type' => 'int'),
	);
	protected $relations = array(
		'node_to_revision' => array(
			'type' => 'one_to_one',
			'my_fields' => array('node_id', 'revision_num'),
			'table' => 'node_revisions',
			'their_fields' => array('node', 'number'),
			'checks' => array(
				'other.number' => 'revnum'
			),
			'object' => array('class' => 'Node_Revision', 'property' => 'revision')
		),
		'node_to_options' => array(
			'type' => 'one_to_many',
			'my_fields' => array('node_id'),
			'table' => 'node_options',
			'their_fields' => array('node_id'),
			'object' => array('class' => 'Node_Option', 'property' => '_options')
		)
	);
}

class Node_Revision extends Model {
	protected $table = 'node_revisions';
	protected $fields = array(
		'id' => array('type' => 'int'),
		'node' => array('type' => 'int'),
		'number' => array('type' => 'int'),
		'content' => array('type' => 'string'),
		'time' => array('type' => 'int'),
	);
	protected $relations = array();
}

class Node_Option extends Model {
	protected $table = 'node_options';
	protected $fields = array(
		'id' => array('type' => 'int'),
		'node_id' => array('type' => 'int'),
		'option_name' => array('type' => 'string'),
		'option_value' => array('type' => 'string'),
	);
	protected $relations = array();	
}

$node = new Node($global);
$node->parent = 1;
//$node->revnum = 1;
$node->type = 'page';
var_dump($node->read());
var_dump($global['db']->num_queries);
