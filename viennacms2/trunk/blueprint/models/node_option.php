<?php
class Node_Option extends Model {
	protected $table = 'node_options';
	protected $keys = array('node_id', 'option_name');
	protected $fields = array(
		'id' => array('type' => 'int'),
		'node_id' => array('type' => 'int'),
		'option_name' => array('type' => 'string'),
		'option_value' => array('type' => 'string'),
	);
	protected $relations = array();	
	
	public function __toString() {
		return $this->option_value;
	}
}