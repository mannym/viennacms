<?php
class VUser extends Model {
	public $may_not_cache = true;
	
	protected $table = 'users';
	protected $keys = array('user_id');
	protected $fields = array(
		'user_id' => array('type' => 'int'),
		'username' => array('type' => 'string'),
		'user_password' => array('type' => 'string'),
		'user_email' => array('type' => 'string'),
		'user_active' => array('type' => 'int'),
		'user_permissions' => array('type' => 'string')
	);
	protected $relations = array();
	
	public function to_acl_id() {
		return 'user:' . $this->user_id;
	}
}