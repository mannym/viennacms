<?php
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

include(ROOT_PATH . 'config.php');
$step = $_REQUEST['step']; 

if (empty($step)) {
	$step = 1;
}

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

switch ($step) {
	case 1:
	case 0:
		$template->assign_vars(array('step' => '1'));
	break;
	case 2:
		$template->assign_vars(array('step' => '2'));
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
			
			case 86:
				$sql[] = 'ALTER TABLE ' . NODES_TABLE . ' ADD `order` INT( 11 ) NOT NULL';
				
			case 89:
				$sql[] = 'ALTER TABLE ' . NODES_TABLE . ' CHANGE `order` `node_order` INT( 11 ) NOT NULL';
		}
		$db->sql_return_on_error(true);
		$mes = '';
		$all = true;
		foreach ($sql as $query) {
			$mes .= '<br />' . $query . ' [';
			
			$success = $db->sql_query($query);
			
			if ($success) {
				$mes .= '<span style="color: green;">' . __('Success') . '</span>';
			} else {
				$all = false;
				$mes .= '<span style="color: red;">' . __('Failed') . '</span><br />' . mysql_error();
			}
			
			$mes .= ']';
			/*if(!$db->sql_query($query)){
				install_die("Could not insert SQL. Error: " . mysql_error());	
			}*/	
		}	
		$db->sql_return_on_error(false);
		
		if ($all) {
			 $sql = 'UPDATE ' . NODE_OPTIONS_TABLE . " SET option_value = '" . $dbversion['new'] . "' WHERE node_id = 0 AND option_name = 'database_version'";
			 $db->sql_query($sql);
		}
		
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