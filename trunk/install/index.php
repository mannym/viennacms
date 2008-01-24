<?php
define('IN_VIENNACMS', true);
define('IN_INSTALL', true);
include('../start.php');
if (!file_exists(ROOT_PATH . 'config.php') && is_writeable(ROOT_PATH)) {
	@fclose(@fopen(ROOT_PATH . 'config.php','w'));
}
include(ROOT_PATH . 'config.php');
$step = $_REQUEST['step']; 

if (empty($step)) {
	$step = 1;
}

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
));

switch ($step) {
	case 1:
	case 0:
		$template->assign_vars(array('step' => '1'));
		$disabled = false;
		$check = array(
			'config.php', 'cache/'
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
		//install_die('grr');
		$dbhost = $_POST['host'];
		$dbuser = $_POST['username'];
		$dbpasswd = $_POST['password'];
		$dbname = $_POST['database'];
		$table_prefix = $_POST['prefix'];
		$name2 = addslashes($_POST['name2']);
		$ww2 = md5($_POST['ww2']);
		$db = database::getnew();
		$db->sql_connect($dbhost, $dbuser, $dbpasswd, $dbname);
		$db->prefix = $table_prefix;
		utils::write_config($dbhost, $dbuser, $dbpasswd, $dbname, $table_prefix);	
		include(ROOT_PATH . 'includes/sql_parse.php');
		$dbms_schema = 'install.sql';
		$sql_query = file_get_contents($dbms_schema);
		$sql_query = preg_replace('#viennacms_#is', $table_prefix, $sql_query);		
		$sql_query = remove_comments($sql_query);
		$sql_query = remove_remarks($sql_query);
		$sql = split_sql_file($sql_query,';');
		unset($sql_query);
		include(ROOT_PATH . "includes/constants_core.php");
		$csql = "SELECT * FROM " . NODES_TABLE;
		$installed = false;
		// no dbal here, we need to do raw checks
		if (@mysql_query($csql)){
			$installed = true;
		} else {
			$error = mysql_error();
			if(!preg_match("#" . preg_quote("#1146 - Table '(.*?)' doesn't exist","#") . "#",$error)){
				$installed = false;
			}else{
				$installed = true;
			}
		}
		if($installed){
			install_die(__('viennaCMS is already installed there. Please try another database or table prefix.'));
		}
		$sql[] = "UPDATE ".USER_TABLE." SET username = '$name2', password = '$ww2'";		

		foreach ($sql as $query) {
			if(!$db->sql_query($query)){
				install_die("Could not insert SQL. Error: " . mysql_error());	
			}	
		}	
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