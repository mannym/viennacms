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

$do_output_page = true;

if(isset($_GET['sql_report'])) {
	$user = user::getnew();
	$user->checkacpauth(false);
	if($user->user_logged_in) {
		$db->sql_report('display');
		$do_output_page = false;
	}
	else {
		$do_output_page = true;
	}
}

if($do_output_page) {
	ob_start();	
	$template->display('main');
	$content = ob_get_contents();
	ob_end_flush();
}

if ($do_output_page && ($config['caching_type'] == 'normal' || $config['caching_type'] == 'aggressive')) {
	utils::get_types();
	
	$do = true;
	
	if ($config['caching_type'] == 'normal' && utils::$types[$page->node->type]['type'] == NODE_MODULES) {		
		foreach ($page->node->revision->modules as $location) {
			foreach ($location as $module) {
				$func = 'dynamic_' . $module['module'];
				$ext = utils::load_extension($module['extension']);
				
				if (method_exists($ext, $func)) {
					$do = (!$ext->$func());
				}
			}
		}		
	}

	if ($do) {
		$pages = $cache->get('_page_output');
		$pages[$page->pagehash] = array(
			'expire' => (time() + $config['caching_time']),
			'output' => base64_encode($content)
		);
		
		$cache->put('_page_output', $pages);
	}
}
?>