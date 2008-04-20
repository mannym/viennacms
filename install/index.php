<?php
/**
* Install/upgrade system
* 
* @package install
* @author viennacms.nl
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

define('IN_VIENNACMS', true);
define('IN_INSTALL', true);
include('../start.php');
include(ROOT_PATH . 'includes/functions_install.php');


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

if (!file_exists(ROOT_PATH . 'config.php') && is_writeable(ROOT_PATH)) {
	@fclose(@fopen(ROOT_PATH . 'config.php','w'));
}
include(ROOT_PATH . 'config.php');
$step = (isset($_REQUEST['step'])) ? $_REQUEST['step'] : 1; 

$steps = array(
	1 => __('Welcome to the viennaCMS installation'),
	2 => __('Input your data'), 
	3 => __('Installation complete!')
);

$template->root = ROOT_PATH . 'install/tpl/';

$template->set_filename(
	'body', 'install.php'
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
		$disabled = false;
		$check = array(
			'config.php', 'cache/', 'files/'
		);
		$files = array();
		foreach ($check as $wa) {
			if(!file_exists(ROOT_PATH . $wa)){
				$disabled = true;
				$txt = "<div style='color:red;'>".__('Does not exist')."</div>";			
			}elseif(!is_writeable(ROOT_PATH . $wa)){
				$disabled = true;
				$txt = "<div style='color:red;'>".__('No')."</div>";
			}else{
				$txt = "<div style='color:green;'>".__('Yes')."</div>";	
			}			
			$files[$wa] = $txt;
		}
		$mes = '';
		if (!version_compare(phpversion(), "5.2.0", ">=")) {
			$mes .= "<br /><strong><div style='color:red;'>";
			$mes .= sprintf(__('The current PHP version (%s) is too old to run viennaCMS correctly. The minimum version is 5.2.0.'),phpversion());
			$mes .= "</div></string>";
			$disabled = true;
		}
		$template->assign_vars(array(
			"disabled" => ($disabled) ? " disabled='disabled'" : '',
			"message" => $mes,
			'files' => $files
		));
	break;
	case 2:
		$template->assign_vars(array('step' => '2'));
	break;
	case 3:
		$template->assign_vars(array('step' => '3'));
		$dbhost = $_POST['database_host'];
		$dbuser = $_POST['database_username'];
		$dbpasswd = $_POST['database_password'];
		$dbname = $_POST['database_name'];
		$table_prefix = $_POST['table_prefix'];
		$admin_username = addslashes($_POST['admin_username']);
		$admin_password1 = md5($_POST['admin_password']);
		$admin_password2 = md5($_POST['admin_password_confirm']);
		if($admin_password1 != $admin_password2)
		{
			$pass_error = true;
			$template->assign_vars(array('step' => '2'));
			break;
		}
		$db = database::getnew();
		$db->sql_connect($dbhost, $dbuser, $dbpasswd, $dbname);
		$db->prefix = $table_prefix;
		$result = install_database($db->prefix, $admin_username, $admin_password1);
		if ($result) { // got an error
			install_die($result);
		}
		utils::config_file_write($dbhost, $dbuser, $dbpasswd, $dbname, $table_prefix);	
	break;	
	case 4:
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