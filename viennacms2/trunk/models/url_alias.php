<?php
class URL_Alias extends Model {
	protected $table = 'url_aliases';
	protected $keys = array('alias_id');
	protected $fields = array(
		'alias_id' => array('type' => 'int'),
		'alias_url' => array('type' => 'string'),
		'alias_target' => array('type' => 'string'),
		'alias_flags' => array('type' => 'int')
	);
	protected $relations = array();
	public $cache = 7200;
}