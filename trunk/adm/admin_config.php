<?php
/**
 * Settings page for the ACP of the viennaCMS
 * 
 * @package viennaCMS
 * @author viennacms.nl
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License 
 */

define("IN_VIENNACMS", true);
define('IN_ADMIN', true);
define('IN_CONFIG', true);
include('../start.php');
$user = user::getnew();
$user->checkacpauth();
$display_admin_tree = (empty($_GET['display_admin_tree']) ) ?  1 : 0;
$mode = (isset($_GET['mode'])) ? $_GET['mode'] : '';
$do = (isset($_GET['do'])) ? $_GET['do'] : 'show';
$page_title = __('viennaCMS ACP');
include('./header.php');

switch ($mode) {
	case 'performance':
		$forms = array(
			__('Page caching') => array(
				'caching_type' => array(
					'type'			=> 'radio',
					'name'			=> 'caching_type',
					'title'			=> __('Caching type'),
					'description'	=> __('The \'simple\' cache option is the most safe option, and is currently the recommended one. The \'normal\' option will cache all page output, except for pages with <em>dynamic modules</em>. Dynamic blocks will still be cached, so minor side-effects may occur. The \'aggressive\' cache option will cache every page in the front end, until it expires or is modified.'),
					'value'			=> array(
						'simple' => array(
							'title' => 'Simple (most safe)',
							'selected' => ($config['caching_type'] == 'simple' || empty($config['caching_type']))
						),
						'normal' => array(
							'title' => 'Normal (minor side-effects with blocks)',
							'selected' => ($config['caching_type'] == 'normal')
						),
						'aggressive' => array(
							'title' => 'Aggressive (can cause problems)',
							'selected' => ($config['caching_type'] == 'aggressive')
						)
					)
				),
				'caching_time' => array(
					'type'			=> 'selectbox',
					'name'			=> 'caching_time',
					'title'			=> __('Output cache time'),
					'description'	=> __('The time the cache of the \'normal\' or \'agressive\' option keeps existing if there are no changes.'),
					'value'			=> utils::get_hours($config['caching_time'])
				)
			)
		);
	break;
	default:
		if ($do == 'show') {
			$do = 'nothing';
			echo __('Select a configuration page in the menu to your left.');
		}
	break;
}

switch ($do) {
	case 'save':
		foreach ($_POST as $key => $value) {
			utils::set_config($key, $value);
		}
		echo __('Settings are saved.');
	break;
	case 'show':
		$form = utils::load_extension('form');
		foreach ($forms as $title => $frm) {
			$form->action = '?do=save';
			$form->submit = __('Save');
			$form->setformfields($frm);
			$form->title = $title;
			$form->generateform();
			echo $form->content;
		}
	break;
}

include('./footer.php');

?>