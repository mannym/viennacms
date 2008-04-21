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

ob_start();	
$template->display('main');
$content = ob_get_contents();
ob_end_flush();

$pages = $cache->get('_page_output');
$pages[$page->pagehash] = array(
	'expire' => (time() + 1800),
	'output' => base64_encode($content)
);

$cache->put('_page_output', $pages);
?>