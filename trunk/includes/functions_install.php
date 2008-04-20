<?php
function install_database($table_prefix, $admin_username, $admin_password) {
	$db = database::getnew();
	include(ROOT_PATH . 'includes/sql_parse.php');
	$dbms_schema = ROOT_PATH . 'install/install.sql';
	$sql_query = file_get_contents($dbms_schema);
	$sql_query = preg_replace('#viennacms_#is', $table_prefix, $sql_query);		
	$sql_query = remove_comments($sql_query);
	$sql_query = remove_remarks($sql_query);
	$sql = split_sql_file($sql_query,';');
	unset($sql_query);
	include(ROOT_PATH . "includes/constants_core.php");
	$csql = "SELECT * FROM " . CONFIG_TABLE;
	$installed = false;
	$db->sql_return_on_error(true);
	if ($db->sql_query($csql) != false){
		$installed = true;
	}
	
	if($installed){
		return __('viennaCMS is already installed there. Please try another database or table prefix.');
	}
	
	$language = '';
	
	if (isset($_COOKIE['language'])) {
		if ($_COOKIE['language'] != 'english') {
			$language = addslashes($_COOKIE['language']);
		}
	}
	$db->sql_return_on_error(true);
	$sql[] = "UPDATE ".USER_TABLE." SET username = '$admin_username', password = '$admin_password', lang = '$language'";		
	include(ROOT_PATH . 'includes/version.php');
	$sql[] = "INSERT INTO " . CONFIG_TABLE . " SET config_name = 'database_version', config_value = '$database_version'";

	foreach ($sql as $query) {
		if(!$db->sql_query($query)){
			return "Could not insert SQL. Error: " . $db->sql_error();	
		}	
	}
	$db->sql_return_on_error(false);
	return false;
}
?>