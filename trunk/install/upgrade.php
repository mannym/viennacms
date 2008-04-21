<?php
/**
 * Upgrade file for the viennaCMS
 * 
 * @package viennaCMS
 * @author viennaCMS developpers
 */

define('IN_VIENNACMS', true);
define('IN_UPGRADE', true);
include('../start.php');

$dbversion = utils::get_database_version();
if ($dbversion['uptodate']) {
	header('Location: ../');
	exit;
}

// check language
if (isset($_GET['language'])) {
	setcookie('language', $_GET['language'], time() + 3600, '/', '');
}

if (isset($_COOKIE['language']) || isset($_GET['language'])) {
	$language = (isset($_GET['language'])) ? $_GET['language'] : $_COOKIE['language'];

	if ($language != 'english') {
		// Set language to $language
		_setlocale(LC_ALL, $language);
		// Specify location of translation tables
		_bindtextdomain("viennacms", ROOT_PATH . "locale");
		// Choose domain
		_textdomain("viennacms");
	}
}

$dir = scandir(ROOT_PATH . 'locale');
$languages = array();

foreach ($dir as $file) {
	if (file_exists(ROOT_PATH . 'locale/' . $file . '/LC_MESSAGES')) {
		$languages[] = $file;
	}
}

$step = (isset($_REQUEST['step'])) ? $_REQUEST['step'] : 1; 

$steps = array(
	1 => __('Welcome to the viennaCMS upgrade page'),
	2 => __('Upgrade data')
);

$template->root = ROOT_PATH . 'install/tpl/';

$template->set_filename(
	'body', 'upgrade.php'
);

$template->assign_vars(array(
	//Template vars
	'stepname' => $steps[$step],
	'total_step' => (count($steps)),
	'languages' => $languages
));
//var_dump($dbversion);
switch ($step) {
	case 1:
	case 0:
		$template->assign_vars(array('step' => '1'));
	break;
	case 2:
		$template->assign_vars(array('step' => '2'));
		$sql = array();
		
		switch ($dbversion['current']) {
			case 0:
				$sql[] = 'ALTER TABLE ' . DOWNLOADS_TABLE . ' CHANGE `time` `time` INT(11) NOT NULL';
				$sql[] = 'INSERT INTO ' . NODE_OPTIONS_TABLE . " SET node_id = 0, option_name = 'database_version', option_value = '1'";
			// no break
			case 59:
				$sql[] = 'CREATE TABLE `' . CONFIG_TABLE . '` (
  `config_name` varchar(255) NOT NULL,
  `config_value` text NOT NULL,
  PRIMARY KEY  (`config_name`)
);';
			// no break
			case 80:
				$sql[] = 'ALTER TABLE `' . DOWNLOADS_TABLE . '` CHANGE `download_id` `download_id` INT( 15 ) NOT NULL AUTO_INCREMENT';
				$sql[] = 'ALTER TABLE `' . DOWNLOADS_TABLE . '` CHANGE `file_id` `file_id` INT( 15 ) NOT NULL';
			// no break
			
			case 89:
				$sql[] = 'ALTER TABLE ' . NODES_TABLE . ' ADD `node_order` INT( 11 ) NOT NULL';
			// no break
			case 90:
				$sql[] = 'INSERT INTO ' . CONFIG_TABLE . " (config_name, config_value) VALUES ('database_version', '110')";
				$sql[] = 'DELETE FROM ' . NODE_OPTIONS_TABLE . ' WHERE node_id = 0 AND option_name = \'database_version\'';
			// no break
			
			case 110:
				$sql[] = 'ALTER TABLE ' . USER_TABLE . ' ADD `login_attempts` MEDIUMINT NOT NULL, 
						  ADD `last_login_attempt` INT(11) NOT NULL';
			// no break
		}
		$db->sql_return_on_error(true);
		$mes = '';
		foreach ($sql as $query) {
			$mes .= '<br />' . $query . ' [';
			
			$success = $db->sql_query($query);
			
			if ($success) {
				$mes .= '<span style="color: green;">' . __('Success') . '</span>';
			} else {
				if(!defined('ALL_SUCCES'))
				{
					define('ALL_SUCCES', false);
				}
				$mes .= '<span style="color: red;">' . __('Failed') . '</span><br />' . $db->sql_error();
			}
			
			$mes .= ']';
		}	
		$db->sql_return_on_error(false);
		
		$sql = 'UPDATE ' . CONFIG_TABLE . " SET config_value = '" . $dbversion['new'] . "' WHERE config_name = 'database_version'";
		$db->sql_query($sql);
		
		$template->assign_vars(array('mes' => $mes));
	break;	
	case 3:
		header('Location: ../index.php');
		exit;
	break;
	default:
		install_die(__('Incorrect install step'));
}
$template->display('body');

function install_die($error){
	global $template;
	$template->assign_vars(array(
		"ierr" => $error,
		'disabled' => true
	));

	$template->display('body');
	die;
}
?>