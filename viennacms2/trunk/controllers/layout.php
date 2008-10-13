<?php
class LayoutController extends Controller {
	public function page($content) {
		// assign navigation
		if (isset(cms::$vars['node'])) {
			$this->parents = $this->get_parents(cms::$vars['node']);
		} else {
			$this->parents = array();
		}
		
		$this->view['nav'] = array(
			1 => $this->get_nav(1),
			2 => $this->get_nav(2)
		);
	
		$this->view['content'] = $content;
		$this->view['styles'] = $this->get_styles();
		
		// user stuff
		if (cms::$user->logged_in) {
			$u_lilo = 'user/logout/' . cms::$user->session->session_id;
			$l_lilo = sprintf(__('Logout [ %s ]'), cms::$user->user->username);
		} else {
			$u_lilo = 'user/login';
			$l_lilo = __('Login');
		}
		$this->view['user'] = cms::$user->user;
		$this->view['login_logout_url'] = $this->view->url($u_lilo);
		$this->view['login_logout'] = $l_lilo;
		$this->view['siteurl'] = manager::base();
		$this->view['sitename'] = cms::$vars['sitenode']->title;
		$this->view['sitedescription'] = cms::$vars['sitenode']->description;		
	}
	
	private function get_styles() {
		$styles = array(
			'layouts/' . cms::$vars['style'] . '/stylesheet.css'
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

		if ($nodes[0]->node_id) {
			$newnode = $nodes[0];
			$nodes = array_merge($nodes, $this->_get_parents($newnode));
		}
		
		return $nodes;
	}
	
	private function get_nav($level = 1) {
		$active = array();
		
		if ($level == 1) {
			$node = cms::$vars['sitenode'];
			$nodes = $node->get_children();
		} else if ($level > 1) {
			if (isset($this->parents[$level])) {
				$nodes = $this->parents[$level]->get_siblings_all();
			} else if (isset($this->parents[$level - 1])) {
				$nodes = $this->parents[$level - 1]->get_children();
			}
		}
		foreach ($this->parents as $parent) {
			$active[] = $parent->node_id;
		}
				
		$links = array();
		
		if (!isset($nodes) || !$nodes) {
			return;
		}
		
		$return = '';
		
		foreach ($nodes as $node) {
			$text = $node->title;
			$link = $this->view->url('node/show/' . $node->node_id);
		
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
