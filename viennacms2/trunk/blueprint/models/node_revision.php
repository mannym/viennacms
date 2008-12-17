<?php
class Node_Revision extends Model {
	protected $table = 'node_revisions';
	protected $keys = array('id');
	protected $fields = array(
		'id' => array('type' => 'int'),
		'node' => array('type' => 'int'),
		'number' => array('type' => 'int'),
		'content' => array('type' => 'string'),
		'time' => array('type' => 'int'),
	);
	protected $relations = array();
}