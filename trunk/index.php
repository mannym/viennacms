<?php
/**
* Main front end loader file for viennaCMS.
* 
* @package viennaCMS
* @author viennacms.nl
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

define('IN_VIENNACMS', true);
include('./start.php');

$Header = '<base href="' . utils::base() . '" />' . "\r\n";

$page = page::getnew();
$page->assign_nav();

utils::run_hook_all('before_display');

$template->set_filename('main', 'index.php');

$template->assign_vars(array(
	'head' => $Header,
	'homeurl' => $page->get_link($page->sitenode)
));
	
$template->display('main');
?>