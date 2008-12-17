<?php
class Session extends Model {
	protected $table = 'sessions';
	protected $keys = array('session_id');
	protected $fields = array(
		'session_id' => array('type' => 'string'),
		'user_id' => array('type' => 'int'),
		'session_ip' => array('type' => 'string'),
		'session_time' => array('type' => 'int'),
	);
	protected $relations = array(
		'session_to_user' => array(
			'type' => 'one_to_one',
			'my_fields' => array('user_id'),
			'table' => 'users',
			'their_fields' => array('user_id'),
			'object' => array('class' => 'User', 'property' => 'user')
		)
	);
}