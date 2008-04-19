<?php
function install_database($table_prefix, $name2, $ww2) {
	$db = database::getnew();
	$db->sql_return_on_error(true);
	include(ROOT_PATH . 'includes/sql_parse.php');
	$dbms_schema = ROOT_PATH . 'install/install.sql';
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
		return __('viennaCMS is already installed there. Please try another database or table prefix.');
	}
	
	$language = '';
	
	if (isset($_COOKIE['language'])) {
		if ($_COOKIE['language'] != 'english') {
			$language = addslashes($_COOKIE['language']);
		}
	}
	
	$sql[] = "UPDATE ".USER_TABLE." SET username = '$name2', password = '$ww2', lang = '$language'";		
	include(ROOT_PATH . 'includes/version.php');
	$sql[] = "INSERT INTO " . CONFIG_TABLE . " SET config_name = 'database_version', config_value = '$database_version'";

	foreach ($sql as $query) {
		if(!$db->sql_query($query)){
			return "Could not insert SQL. Error: " . mysql_error();	
		}	
	}
	$db->sql_return_on_error(false);
	return false;
}
?>