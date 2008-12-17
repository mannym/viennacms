<?php
class Permission_Object extends Model {
	protected $table = 'permission_objects';
	protected $keys = array('id');
	protected $fields = array(
		'id' => array('type' => 'int'),
		'resource' => array('type' => 'string'),
		'owner_id' => array('type' => 'int'),
		'group_id' => array('type' => 'int'),
		'permission_mask' => array('type' => 'string')
	);
	protected $relations = array(
		'owner' => array(
			'type' => 'one_to_one',
			'my_fields' => array('owner_id'),
			'table' => 'users',
			'their_fields' => array('user_id'),
			'object' => array('class' => 'User', 'property' => 'owner')
		)
	);
}