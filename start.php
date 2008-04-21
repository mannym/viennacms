<?php
/**
* Loader file for viennaCMS.
* "Start it up before it ends up dead" -- me :)
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

define('ROOT_PATH', dirname(__FILE__) . '/');
error_reporting(E_ALL & ~E_NOTICE);

// If we are on PHP >= 6.0.0 we do not need some code
if (version_compare(PHP_VERSION, '6.0.0-dev', '>='))
{
	/**
	* @ignore
	*/
	define('STRIP', false);
}
else
{
	set_magic_quotes_runtime(0);

	define('STRIP', (get_magic_quotes_gpc()) ? true : false);
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

/**
* Load pre-dependencies. 
*/
include(ROOT_PATH . 'config.php');
include(ROOT_PATH . 'includes/gettext.php');
include(ROOT_PATH . 'includes/db/' . $dbms . '.php');
include(ROOT_PATH . 'includes/acm_file.php');
include(ROOT_PATH . 'includes/utils.php');
include(ROOT_PATH . 'includes/template.php');
include(ROOT_PATH . 'includes/nodetree.php');
include(ROOT_PATH . 'includes/page.php');
include(ROOT_PATH . 'includes/user.php');

/**
* Set error handler. 
*/

set_error_handler(array('utils', 'handle_error'));

/**
* And load everything :) 
*/
$cache = new acm();
$db = database::getnew();
$template = template::getnew();
if (!defined('IN_INSTALL') && !defined('IN_UPGRADE')) {
	$version = utils::get_database_version();
	if (!$version['uptodate']) {
		if (stripos($_SERVER['DOCUMENT_ROOT'], 'adm') !== false) {
			header('Location: ../install/upgrade.php');
		} else {
			header('Location: ' . utils::base() . 'install/upgrade.php');
		}
		exit;
	}
	
	$sql = 'SELECT * FROM ' . CONFIG_TABLE;
	$result = $db->sql_query($sql);
	$config = array();

	while ($row = $db->sql_fetchrow($result)) {
		$config[$row['config_name']] = $row['config_value'];
	}
}

utils::run_hook_all('init');
register_shutdown_function('shutdown_cleanly');
?>