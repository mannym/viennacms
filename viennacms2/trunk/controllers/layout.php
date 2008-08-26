<?php
class LayoutController extends Controller {
	public function page($content) {
		// assign navigation
		if (isset($this->global['node'])) {
			$this->parents = $this->get_parents($this->global['node']);
		} else {
			$this->parents = array();
		}
		
		$this->view['nav'] = array(
			1 => $this->get_nav(1),
			2 => $this->get_nav(2)
		);
	
		$this->view['content'] = $content;
		$this->view['styles'] = $this->get_styles();
	}
	
	private function get_styles() {
		$styles = array(
			'layouts/' . $this->global['style'] . '/stylesheet.css'
		);
		$return = '';
		
		foreach ($styles as $style) {
			$return .= '<link href="' . manager::base() . $style . '" rel="stylesheet" type="text/css" />';
		}
		
		return $return;
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
		array_pop($array); // remove the main node
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
