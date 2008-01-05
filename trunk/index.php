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

$Header = '<base href="' . utils::base() . '" />' . "\r\n";

$template->set_filename('main', 'index.php');

$template->assign_vars(array(
	'title' => $page->node->title,
	'sitename' => $page->sitenode->title,
	'crumbs' => $page->make_breadcrumbs(),
	'right' => $page->get_loc('right'),
	'middle' => $page->get_loc('middle'),
	'left' => $page->get_loc('left'),
	'head' => $Header,
));
	
$template->display('main');
?>