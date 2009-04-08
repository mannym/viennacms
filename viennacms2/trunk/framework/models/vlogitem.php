<?php
class VLogItem extends Model {
	protected $table = 'log';
	protected $keys = array('log_id');
	protected $fields = array(
		'log_id' => array('type' => 'int'),
		'log_source' => array('type' => 'string'),
		'log_type' => array('type' => 'string'),
		'log_message' => array('type' => 'string'),
		'log_time' => array('type' => 'int'),
		'log_user' => array('type' => 'int')
	);
	protected $relations = array();
}