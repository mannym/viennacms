<?php
class Node extends Model {
	static public function get_types() {
		$return = array();
		
		$types = cms::$registry->get_types('Node');
		foreach ($types as $int => $type) {
			$class = new $type();
			$data = $class->get_typedata();
			
			$return[$int] = $data;
		}
		
		return $return;
	}
	
	static $searchpaths = array();
	
	static public function autoload($class_name) {
		if (substr($class_name, -4) == 'Node' && strlen($class_name) > 5) {
			$name = strtolower(substr($class_name, 0, -4));

			$files = array();
		
			foreach (self::$searchpaths as $path) {
				$files[] = $path . $name . '.php';
			}
			
			$filename = cms::scan_files($files);
			
			if (file_exists($filename)) {
				include_once($filename);
				return true;
			}
			
			return false;
		}
	}
	
	public $is_legacy = true;
	
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
	
	public function __construct() {
		$class = get_class($this);
		
		if ($class != 'Node') {
			$this->type = strtolower(str_replace('Node', '', $class));
		}
	}
	
	public static function open($node_id) {
		$node = new Node();
		$node->node_id = $node_id;
		$node = $node->read();
		
		return $node[0];
	}
	
	public static function exists($search) {
		$node = new Node();
		
		if (is_numeric($search)) {
			$node->node_id = $search;
		} else if (is_string($search)) {
			$node->title = $search;
		}
		
		$node->cache = 86400;
		$node = $node->read();
		
		return (count($node) > 0);
	}
	
	protected function hook_read() {
		$this->revision->node_obj = $this;
		$this->options = new Node_Options();
		$this->options->node = $this;
		foreach ($this->_options as $value) {
			$this->options[$value->option_name] = $value;
		}
		
		$this->set_type_vars();
		
		//manager::run_hook_all('node_read', $this);
		VEvents::invoke('node.hook-read', $this);
	}
	
	protected function hook_copy_data(Model $source) {
		if ($source->open_readonly) {
			$this->options = $source->options;
		}
	}
	
	protected function hook_remove() {
		$revision = new Node_Revision();
		$revision->node = $this->node_id;
		$revisions = $revision->read();
		
		foreach ($revisions as $revision) {
			$revision->delete();
		}
		
		//manager::run_hook_all('node_remove', $this);
		VEvents::invoke('node.remove', $this);
	}
	
	protected function hook_preremove() {
		//manager::run_hook_all('node_pre_remove', $this);
		VEvents::invoke('node.pre-remove', $this);
	}
	
	protected function hook_new() {
		$this->options = new Node_Options();
		$this->options->node = $this;
		$this->_options = array();
		
		//manager::run_hook_all('node_pre_new', $this);
		VEvents::invoke('node.create', $this);
	}
	
	// needed, as this function takes up a _lot_ of time!
	static $typescache = array();
	
	protected function hook_get_type($row) {
		if (isset(Node::$typescache[$row['type']])) {
			return Node::$typescache[$row['type']];
		}
		
		$type = $row['type'];
		$class = ucfirst($type) . 'Node';
		
		if (class_exists($class)) {
			Node::$typescache[$row['type']] = $class;
			
			return $class;
		}
		
		Node::$typescache[$row['type']] = false;
		
		return false;
	}
	
	public function has_references() {
		//$results = manager::run_hook_all('node_check_references', $this);
		$results = VEvents::invoke('node.has-references', $this);
		$has = false;
		
		foreach ($results as $result) {
			if ($result) {
				$has = true;
				break;
			}
		}
		
		return $has;
	}
	
	public function set_type_vars() {
		if ($this->is_legacy) {
			$typedata = Node::get_types();
			
			if (isset($typedata[$this->type])) {
				$this->typedata = $typedata[$this->type];
			}
		} else {
			$class = ucfirst($this->type) . 'Node';
			
			$this->typedata = call_user_func(array(new $class(), 'get_typedata'));
		}
	}

	protected function hook_presave() {
		$this->revision_num++;
		
		if (!$this->written) {
			$this->created = time();
		}
		
		//manager::run_hook_all('node_pre_save', $this);
		VEvents::invoke('node.pre-save', $this);
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
		
		//manager::run_hook_all('node_save', $this);
		VEvents::invoke('node.save', $this);
	}
	
	public function get_children($cache = false) {
		$node = new Node();
		$node->parent = $this->node_id;
		$node->cache = $cache;
		$node->open_readonly = $this->open_readonly;
		return $node->read();
	}
	
	public function get_parent($cache = false) {
		$node = new Node();
		$node->node_id = $this->parent;
		$node->cache = $cache;
		$node->open_readonly = $this->open_readonly;
		$node->read(true);
		return $node;
	}

	private static $sibling_cache = array();
	private static $parents_cache = array();

	public function get_parents($cache = false) {
		if (!empty(Node::$parents_cache[$this->node_id])) {
			return Node::$parents_cache[$this->node_id];
		}
		
		$array = array($this);
		$array = array_merge($array, $this->_get_parents($this, $cache));
		array_pop($array); // remove the main node
		$parents = array_reverse($array);
		
		Node::$parents_cache[$this->node_id] = $parents;
		
		return $parents;
	}
	
	private function _get_parents($node, $cache) {
		$nodes = array($node->get_parent($cache));

		if ($nodes[0]->node_id) {
			$newnode = $nodes[0];
			$nodes = array_merge($nodes, $this->_get_parents($newnode, $cache));
		}
		
		return $nodes;
	}
	
	public function get_siblings_all($cache = false) {
		if (!empty(Node::$sibling_cache[$this->parent])) {
			return Node::$sibling_cache[$this->parent];
		}

		$node = new Node();
		$node->parent = $this->parent;
		$node->cache = $cache;
		$node->open_readonly = $this->open_readonly;
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
		
		$types = Node::get_types();
		foreach ($types as $key => $type) {
			$classname = $key . 'Node';
			$node = new $classname();
			
			//$node->type = $key;
			
			if (cms::display_allowed('this_under_other', $node, $this)) {
				$return[] = $key;
			}
		}	
		
		return $return;
	}
	
	public function to_url() {
		return 'node/show/' . $this->node_id;
	}
	
	public function to_admin_url() {
		$url = new stdClass;
		$url->url = 'admin/controller/node/edit/' . $this->node_id;
		
		$override = VEvents::invoke('core.alter-tree-url', 'admin_tree', $url, '', $this);
		
		return $url->url;
	}
	
	public function to_acl_id() {
		return 'node:' . $this->node_id;
	}
}

class NodeResourceHandler implements IResourceHandler {
	private function get_node($acl_id) {
		$data = cms::$auth->parse_acl_id($acl_id);
		
		$node = new Node();
		$node->node_id = $data['id'];
		$nodes = $node->read();

		return $nodes[0];
	}
	
	public function get_parents($resource) {
		$node = $this->get_node($resource);
		$parents = $node->get_parents(3600);
		
		$return = array();
		foreach ($parents as $parent) {
			$return[] = $parent->to_acl_id();
		}
		
		return $return;
	}
	
	public function get_default_rights($resource) {
		return array(
			'group:meta-everyone' => array(
				'list' => ACL_SETTING_ALLOW,
				'read' => ACL_SETTING_ALLOW,
				'create' => ACL_SETTING_UNSET,
				'modify' => ACL_SETTING_UNSET,
				'modify_essential' => ACL_SETTING_UNSET
			),
			// TODO: this is placeholder
			'user:1' => array(
				'list' => ACL_SETTING_ALLOW,
				'read' => ACL_SETTING_ALLOW,
				'create' => ACL_SETTING_ALLOW,
				'modify' => ACL_SETTING_ALLOW,
				'modify_essential' => ACL_SETTING_ALLOW
			),
		);
	}
}