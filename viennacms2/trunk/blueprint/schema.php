<?php
// TODO: move to be dynamically created from models in 2.1 or so
$schema_data = array();

$schema_data['viennacms_nodes'] = array(
	'COLUMNS' => array(
		'node_id' => array('UINT', NULL, 'auto_increment'),
		'title' => array('VCHAR:200', ''),
		'description' => array('TEXT_UNI', ''),
		'type' => array('VCHAR:50', ''),
		'parent' => array('UINT', 0),
		'revision_num' => array('UINT', 0),
		'created' => array('TIMESTAMP', 0)
	),
	'PRIMARY_KEY' => 'node_id',
	'KEYS' => array(
		'parent' => array('INDEX', 'parent')
	) 
);

$schema_data['viennacms_node_options'] = array(
	'COLUMNS' => array(
		'id' => array('UINT', NULL, 'auto_increment'),
		'node_id' => array('UINT', 0),
		'option_name' => array('VCHAR:100', ''),
		'option_value' => array('MTEXT_UNI', '')
	),
	'PRIMARY_KEY' => 'id',
	'KEYS' => array(
		'node_id' => array('INDEX', 'node_id')
	) 
);

$schema_data['viennacms_node_revisions'] = array(
	'COLUMNS' => array(
		'id' => array('UINT', NULL, 'auto_increment'),
		'node' => array('UINT', 0),
		'number' => array('UINT', 0),
		'content' => array('MTEXT_UNI', ''),
		'time' => array('TIMESTAMP', 0)
	),
	'PRIMARY_KEY' => 'id',
	'KEYS' => array(
		'node_number' => array('INDEX', array('node', 'number'))
	) 
);

$schema_data['viennacms_acl_entries'] = array(
	'COLUMNS' => array(
		'id' => array('UINT', NULL, 'auto_increment'),
		'resource' => array('VCHAR:40', ''),
		'person' => array('VCHAR:40', ''),
		'setting' => array('TINT:1', 0)
	),
	'PRIMARY_KEY' => 'id',
	'keys' => array(
		'object' => array('INDEX', 'resource')
	)
);

// TODO: remove this when ACL transition is completed
$schema_data['viennacms_permission_objects'] = array(
	'COLUMNS' => array(
		'id' => array('UINT', NULL, 'auto_increment'),
		'resource' => array('VCHAR:40', ''),
		'owner_id' => array('UINT', 0),
		'group_id' => array('UINT', 0),
		'permission_mask' => array('VCHAR:60', '')
	),
	'PRIMARY_KEY' => 'id',
	'KEYS' => array(
		'resource' => array('INDEX', 'resource')
	) 
);

$schema_data['viennacms_sessions'] = array(
	'COLUMNS' => array(
		'session_id' => array('VCHAR:40', ''),
		'user_id' => array('UINT', 0),
		'session_ip' => array('VCHAR:160', ''),
		'session_time' => array('TIMESTAMP', 0)
	),
	'PRIMARY_KEY' => 'session_id'
);

$schema_data['viennacms_url_aliases'] = array(
	'COLUMNS' => array(
		'alias_id' => array('UINT', NULL, 'auto_increment'),
		'alias_url' => array('VCHAR:255', ''),
		'alias_target' => array('VCHAR:255', ''),
		'alias_flags' => array('TINT:2', 0)
	),
	'PRIMARY_KEY' => 'alias_id',
	'KEYS' => array(
		'alias_url' => array('INDEX', 'alias_url')
	) 
);

$schema_data['viennacms_users'] = array(
	'COLUMNS' => array(
		'user_id' => array('UINT', NULL, 'auto_increment'),
		'username' => array('VCHAR:100', ''),
		'user_password' => array('VCHAR:50', ''),
		'user_email' => array('VCHAR:150', ''),
		'user_active' => array('BOOL', 0),
		'user_permissions' => array('TEXT', '')
	),
	'PRIMARY_KEY' => 'user_id',
	'KEYS' => array(
		'username' => array('INDEX', 'username')
	) 
);

$schema_data['viennacms_log'] = array(
	'COLUMNS' => array(
		'log_id' => array('UINT', NULL, 'auto_increment'),
		'log_source' => array('VCHAR:160', ''),
		'log_type' => array('VCHAR:20', ''),
		'log_message' => array('TEXT', ''),
		'log_time' => array('TIMESTAMP', 0),
		'log_user' => array('UINT', 0),
	),
	'PRIMARY_KEY' => 'log_id',
	'KEYS' => array(
		'type' => array('INDEX', 'log_type'),
		'source' => array('INDEX', 'log_source')
	)
);