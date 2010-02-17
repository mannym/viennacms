<?php
class VACLEntry extends Model {
	protected $table = 'acl_entries';
	protected $keys = array('id');
	protected $fields = array(
		'id' => array('type' => 'int'),
		'resource' => array('type' => 'string'),
		'person' => array('type' => 'string'),
		'operation' => array('type' => 'string'),
		'setting' => array('type' => 'int')
	);
	protected $relations = array();
}