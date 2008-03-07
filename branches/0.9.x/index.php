<?php
/**
* Main front end loader file for viennaCMS.
* 
* @package viennaCMS
* @author viennainfo.nl
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

define('IN_VIENNACMS', true);
include('./start.php');

$page = page::getnew();
$page->assign_nav();

utils::run_hook_all('before_display');

$Header = '<base href="' . utils::base() . '" />' . "\r\n";

$template->set_filename('main', 'index.php');

$template->assign_vars(array(
	'title' => $page->node->title,
	'description' => $page->node->description,
	'sitename' => $page->sitenode->title,
	'sitedescription' => $page->sitenode->description,
	'crumbs' => $page->make_breadcrumbs(),
	'right' => $page->get_loc('right'),
	'middle' => $page->get_loc('middle'),
	'left' => $page->get_loc('left'),
	'head' => $Header,
	'homeurl' => $page->get_link($page->sitenode)
));
	
$template->display('main');
?>