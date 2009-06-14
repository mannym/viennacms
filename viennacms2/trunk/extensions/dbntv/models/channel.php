<?php
class Channel extends Model {
	protected $table = 'dbntv_channels';
	protected $keys = array('channel_id');
	protected $fields = array(
		'channel_id' => array('type' => 'string'),
		'channel_name' => array('type' => 'string'),
		'channel_url' => array('type' => 'string'),
		'channel_logo' => array('type' => 'string'),
		'channel_country' => array('type' => 'string'),
	);
	protected $relations = array();	
}