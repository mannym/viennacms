<?php
class NodeController extends Controller {
	public function __construct($global) {
		parent::__construct($global);
		$this->global['sitenode'] = $this->get_sitenode();
	}

	public function show() {
		$node = new Node();
		$node->load('id = ?', array(intval($this->arguments[0])));
		
		if (!$node->id) {
			$this->global['manager']->page_not_found();
		}

		$this->view['node'] = $node;
		$this->global['layout']->view['title'] = $node->title;
		
		// assign navigation
		$this->parents = $this->get_parents($node);
		
		$this->global['layout']->view['nav'] = array(
			1 => $this->get_nav(1),
			2 => $this->get_nav(2)
		);
	}
	
	public function main() {
		/*$node = new Node();
		$node->title = 'Woef!';
		$node->parent = 2;
		$node->description = 'De blafnode :D';
		$node->type = 'page';
		$node->revision->content = 'Woef :D';
		$node->save();
		
		$node = new Node();
		$node->load('id = 7');
		echo $node->revision->content;
			
		$node = new Node();
		$node->load('id = 7');
		$node->revision->content = 'Woef x2';
		$node->save();
		*/

		$this->global['router']->parts['action'] = 'show';
		$this->view->reset_path();
		$this->arguments = array((string) $this->global['sitenode']->options['homepage']);
		$this->show();
	}
	
	public function get_sitenode() {
		// create a temporary node to serve as the main root
		$node = new Node();
		$node->id = 0;
		$sites = $node->get_children();
		
		// now check the hostname
		foreach ($sites as $node) {
			if ($node->options['hostname'] == '' && !isset($default)) {
				// save it for the default
				$default = $node;
			} else if ($_SERVER['HTTP_HOST'] == $node->options['hostname']) {
				return $node; // return immediately
			}
		}
		
		return $default;
	}
	
	/**
	* Get all parents of a node. 
	* 
	* @param Node $node
	* @return array $parents
	*/

	public function get_parents($node) {
		$array = array($node);
		$array = array_merge($array, $this->_get_parents($node));
		$parents = array_reverse($array);
		return $parents;
	}
	
	/**
	 * Internal get_parents
	 * 
	 * @param Node $node
	 */
	
	private function _get_parents($node) {
		$nodes = array($node->get_parent());

		if ($nodes[0]->id) {
			$newnode = $nodes[0];
			$nodes = array_merge($nodes, $this->_get_parents($newnode));
		}
		
		return $nodes;
	}
	
	private function get_nav($level = 1) {
		$active = array();
		
		if ($level == 1) {
			$node = $this->global['sitenode'];
			$nodes = $node->get_children();
		} else if ($level > 1) {
			if (isset($this->parents[$level])) {
				$nodes = $this->parents[$level]->get_siblings_all();
			} else if (isset($this->parents[$level - 1])) {
				$nodes = $this->parents[$level - 1]->get_children();
			}
		}
		foreach ($this->parents as $parent) {
			$active[] = $parent->id;
		}
				
		$links = array();
		
		if (!isset($nodes) || !$nodes) {
			return;
		}
		
		$return = '';
		
		foreach ($nodes as $node) {
			$text = $node->title;
			$link = $this->view->url('node/show/' . $node->id);
		
			$class = '';
				
			if (in_array($node->id, $active)) {
				$class = ' class="active"';
			}
				
			$return .= <<<LINK
			<li>
				<a href="$link"$class>$text</a>
			</li>
LINK;
		}
		
		return $return;
	}
}