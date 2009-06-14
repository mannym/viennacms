<?php
class Program extends Model {
	protected $table = 'dbntv_programs';
	protected $keys = array('program_id');
	protected $fields = array(
		'program_id' => array('type' => 'int'),
		'program_start' => array('type' => 'int'),
		'program_end' => array('type' => 'int'),
		'program_title' => array('type' => 'string'),
		'program_description' => array('type' => 'string'),
		'program_channel' => array('type' => 'string'),
		'program_category' => array('type' => 'string'),
	);
	protected $relations = array();	
}