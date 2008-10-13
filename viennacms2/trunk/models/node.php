<?php
class Node extends Model {
	protected $table = 'nodes';
	protected $keys = array('node_id');
	protected $fields = array(
		'node_id' => array('type' => 'int'),
		'title' => array('type' => 'string'),
		'description' => array('type' => 'string'),
		'type' => array('type' => 'string'),
		'parent' => array('type' => 'int'),
		'revision_num' => array('type' => 'int', 'relation' => 'node_to_revision'),
		'created' => array('type' => 'int'),
	);
	protected $relations = array(
		'node_to_revision' => array(
			'type' => 'one_to_one',
			'my_fields' => array('node_id', 'revision_num'),
			'table' => 'node_revisions',
			'their_fields' => array('node', 'number'),
			'checks' => array(
				'other.number' => 'revnum'
			),
			'object' => array('class' => 'Node_Revision', 'property' => 'revision')
		),
		'node_to_options' => array(
			'type' => 'one_to_many',
			'my_fields' => array('node_id'),
			'table' => 'node_options',
			'their_fields' => array('node_id'),
			'object' => array('class' => 'Node_Option', 'property' => '_options')
		)
	);
	
	protected function hook_read() {
		$this->revision->node_obj = $this;
		$this->options = new Node_Options();
		$this->options->node = $this;
		foreach ($this->_options as $value) {
			$this->options[$value->option_name] = $value;
		}
	}
	
	protected function hook_new() {
		$this->options = new Node_Options();
		$this->options->node = $this;
		$this->_options = array();
	}

	protected function hook_presave() {
		$this->revision_num++;
		
		if (!$this->written) {
			$this->created = time();
		}
	}
		
	protected function hook_save() {
		if (!$this->revision->written) {
			$this->revision->node = $this->node_id;
		}
		
		$this->revision->written = false;
		$this->revision->id = 0;
		$this->revision->number = $this->revision_num;
		$this->revision->time = time();
	}
	
	public function get_children() {
		$node = new Node();
		$node->parent = $this->node_id;
		return $node->read();
	}
	
	public function get_parent() {
		$node = new Node();
		$node->node_id = $this->parent;
		$node->read(true);
		return $node;
	}
	
	public function get_siblings_all() {
		$node = new Node();
		$node->parent = $this->parent;
		return $node->read();
	}
}