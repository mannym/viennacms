<?php
class User extends Model {
	protected $table = 'users';
	protected $keys = array('user_id');
	protected $fields = array(
		'user_id' => array('type' => 'int'),
		'username' => array('type' => 'string'),
		'user_password' => array('type' => 'string'),
		'user_email' => array('type' => 'string'),
		'user_active' => array('type' => 'int'),
	);
	protected $relations = array();
}