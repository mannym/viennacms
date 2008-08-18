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
	
	if (!file_exists($filename)) {
		return false;
	}
	
	include_once($filename);
}

if (version_compare(phpversion(), '6.0.0-dev', '<') && get_magic_quotes_gpc()) {
	define('STRIP', true);
} else {
	define('STRIP', false);
}

$global = new GlobalStore();