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
	public $typedata = array();
	
	protected function hook_read() {
		$this->revision->node_obj = $this;
		$this->options = new Node_Options();
		$this->options->node = $this;
		foreach ($this->_options as $value) {
			$this->options[$value->option_name] = $value;
		}
		
		$this->set_type_vars();
	}
	
	protected function hook_remove() {
		$revision = new Node_Revision();
		$revision->node = $this->node_id;
		$revisions = $revision->read();
		
		foreach ($revisions as $revision) {
			$revision->delete();
		}
	}
	
	protected function hook_new() {
		$this->options = new Node_Options();
		$this->options->node = $this;
		$this->_options = array();
	}
	
	public function set_type_vars() {
		$typedata = manager::run_hook_all('get_node_types');
		
		if (isset($typedata[$this->type])) {
			$this->typedata = $typedata[$this->type];
		}
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
			
			foreach ($this->_options as $option) {
				$option->node_id = $this->node_id;
			}
		}
		
		$this->revision->written = false;
		$this->revision->id = 0;
		$this->revision->number = $this->revision_num;
		$this->revision->time = time();
	}
	
	public function get_children($cache = false) {
		$node = new Node();
		$node->parent = $this->node_id;
		$node->cache = $cache;
		return $node->read();
	}
	
	public function get_parent($cache = false) {
		$node = new Node();
		$node->node_id = $this->parent;
		$node->cache = $cache;
		$node->read(true);
		return $node;
	}

	private static $sibling_cache = array();

	public function get_siblings_all($cache = false) {
		if (!empty(Node::$sibling_cache[$this->parent])) {
			return Node::$sibling_cache[$this->parent];
		}

		$node = new Node();
		$node->parent = $this->parent;
		$node->cache = $cache;
		$siblings = $node->read();

		Node::$sibling_cache[$this->parent] = $siblings;

		return $siblings;
	}

	public function next($cache = false) {
		$siblings = $this->get_siblings_all($cache);

		foreach ($siblings as $key => $sibling) {
			if ($sibling->node_id == $this->node_id) {
				return (isset($siblings[$key + 1])) ? $siblings[$key + 1] : false;
			}
		}
	}

	public function previous($cache = false) {
		$siblings = $this->get_siblings_all($cache);

		foreach ($siblings as $key => $sibling) {
			if ($sibling->node_id == $this->node_id) {
				return (isset($siblings[$key - 1])) ? $siblings[$key - 1] : false;
			}
		}
	}
	
	public function possible_child_types() {
		$return = array();
		
		$types = manager::run_hook_all('get_node_types');
		foreach ($types as $key => $type) {
			$node = new Node();
			$node->type = $key;
			
			if (cms::display_allowed('this_under_other', $node, $this)) {
				$return[] = $key;
			}
		}	
		
		return $return;
	}
	
	public function to_url() {
		return 'node/show/' . $this->node_id;
	}
}