<?php
$schema_data = array();
$schema_data['viennacms_config'] = array(
	'COLUMNS' => array(
		'config_name' => array('VCHAR', ''),
		'config_value' => array('TEXT', '')
	),
	'PRIMARY_KEY'=> 'config_name'
);

$schema_data['viennacms_node_options'] = array(
	'COLUMNS' => array(
		'option_id' => array('INT:11', NULL, 'auto_increment'),
		'node_id' => array('INT:11', 0),
		'option_name' => array('VCHAR', ''),
		'option_value' => array('TEXT', '')
	),
	'PRIMARY_KEY' => 'option_id',
	/*'KEYS' => array(
		'node_id' => array('INDEX', 'node_id')
	)*/
);

$schema_data['viennacms_node_revisions'] = array(
	'COLUMNS' => array(
		'revision_id' => array('INT:11', NULL, 'auto_increment'),
		'node_id' => array('INT:11', 0),
		'revision_number' => array('INT:11', 0),
		'node_content' => array('TEXT', ''),
		'revision_date' => array('INT:11', 0)
	),
	'PRIMARY_KEY' => 'revision_id'
);

$schema_data['viennacms_nodes'] = array(
	'COLUMNS' => array(
		'node_id' => array('INT:11', NULL, 'auto_increment'),
		'title' => array('VCHAR', ''),
		'title_clean' => array('VCHAR', ''),
		'parentdir' => array('TEXT', ''),
		'extension' => array('VCHAR:20', ''),
		'description' => array('VCHAR', ''),
		'created' => array('INT:11', 0),
		'type' => array('VCHAR:40', ''),
		'parent_id' => array('INT:11', 0),
		'revision_number' => array('INT:11', 0),
		'node_order' => array('INT:11', 0)
	),
	'PRIMARY_KEY' => 'node_id'
);

$schema_data['viennacms_users'] = array(
	'COLUMNS' => array(
		'userid' => array('INT:10', NULL, 'auto_increment'),
		'username' => array('VCHAR:20', ''),
		'password' => array('VCHAR:32', ''),
		'email' => array('VCHAR:50', ''),
		'lang' => array('VCHAR:6', ''),
		'login_attempts' => array('UINT', 0),
		'last_login_attempt' => array('INT:11', 0),
	),
	'PRIMARY_KEY' => 'userid'
);

$schema_data['viennacms_downloads'] = array(
	'COLUMNS' => array(
		'download_id' => array('INT:11', NULL, 'auto_increment'),
		'file_id' => array('INT:11', 0),
		'ip' => array('VCHAR:15', ''),
		'forwarded_for' => array('VCHAR:15', ''),
		'user_agent' => array('TEXT', ''),
		'referer' => array('TEXT', ''),
		'time' => array('INT:11', 0),
	),
	'PRIMARY_KEY' => 'download_id'
);
?>