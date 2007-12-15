<?php
/**
* Loader file for viennaCMS.
* "Start it up before it ends up dead" -- me :)
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

define('ROOT_PATH', dirname(__FILE__) . '/');
/**
* Load pre-dependencies. 
*/
include(ROOT_PATH . 'includes/gettext.php');
include(ROOT_PATH . 'includes/db/mysql.php');
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

?>