<?php
function install_database($table_prefix, $admin_username, $admin_password, $dbms) {
	global $db;
	$db = database::getnew();
	include(ROOT_PATH . 'includes/sql_parse.php');
	//$dbms_schema = ROOT_PATH . 'install/schemas/' . $dbms . '.sql';
	//$dbms_data = ROOT_PATH . '/install/schemas/schema_data.sql';
	//$sql_query = file_get_contents($dbms_schema);
	//$sql_query = file_get_contents($dbms_data);
	//$sql_query = preg_replace('#viennacms_#is', $table_prefix, $sql_query);		
	//$sql_query = remove_comments($sql_query);
	//$sql_query = remove_remarks($sql_query);
	//$sql = split_sql_file($sql_query,';');
	$sql = array();
	//unset($sql_query);
	include(ROOT_PATH . "includes/constants_core.php");
	$csql = "SELECT * FROM " . CONFIG_TABLE;
	$installed = false;
	$db->sql_return_on_error(true);

	if ($db->sql_fetchrow($db->sql_query($csql)) != false){
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
	
	include(ROOT_PATH . 'includes/db/db_tools.php');
	include(ROOT_PATH . 'install/schemas/schema.php');

	// we must do this so that we can handle the errors
	db_tools::$return_statements = true;

	foreach ($schema_data as $table_name => $table_data)
	{
		// Change prefix
		$table_name = preg_replace('#viennacms_#i', $table_prefix, $table_name);

		$statements = db_tools::sql_create_table($table_name, $table_data);

		foreach ($statements as $sqlt)
		{
			if (!$db->sql_query($sqlt))
			{
				$error = $db->sql_error();
				return 'Could not insert SQL. Error: ' . $error['message'] . ', query: ' . $sqlt;
			}
		}
	}
	
	$db->sql_return_on_error(true);
	$sql[] = "INSERT INTO " . USER_TABLE . " (username, password, lang) VALUES ('$admin_username', '$admin_password', '$language')";		
	include(ROOT_PATH . 'includes/version.php');
	$sql[] = "INSERT INTO " . CONFIG_TABLE . " (config_name, config_value) VALUES ('database_version', '$database_version')";

	foreach ($sql as $query) {
		if(!$db->sql_query($query)){
			$error = $db->sql_error();
			return "Could not insert SQL. Error: " . $error['message'] . ', query: ' . $sqlt;	
		}	
	}
	$db->sql_return_on_error(false);
	
	$node = CMS_Node::getnew();
	$node->title = 'viennaCMS';
	$node->title_clean = 'viennacms';
	$node->description = 'A new viennaCMS installation';
	$node->type = 'site';
	$node->parent_id = 0;
	$node->write();
	return false;
}
?>