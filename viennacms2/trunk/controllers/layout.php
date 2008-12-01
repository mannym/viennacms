<?php
/**
* LayoutController
* Creates the page layout variables, and starts the view.
* 
* @package viennaCMS2
* @version $Id$
* @access public
*/
class LayoutController extends Controller {
	/**
	* LayoutController::page()
	* Assigns variables to the page.php view, including the page content.
	* 
	* @param string $content Content of the page
	* @return void
	*/
	public function page($content) {
		global $starttime;
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
		$this->view['acp_url'] = $this->view->url('admin');
		
		// Has user ACP auth?
		$this->view['acp_auth'] = false;
		$auth = new Auth();
		$rights = $auth->get_rights('admin:see_acp', cms::$user->user);
		
		if (in_array('y', $rights)) {
			$this->view['acp_auth'] = true;
		}
		
		if (defined('DEBUG') || defined('DEBUG_EXTRA')) {
			$debug_output = cms::$db->num_queries['normal'] . ' queries, and ' . cms::$db->num_queries['cached'] . ' cached | ';
			$debug_output .= 'Time: ' . round(microtime(true) - cms::$vars['starttime'], 3) . ' seconds | ';
			$memory_usage = memory_get_usage() - cms::$vars['base_memory_usage'];
			$debug_output .= 'Memory usage: ' . round($memory_usage / 1024, 2) . ' kB';
			if (defined('DEBUG_EXTRA')) {			
				$debug_output .= ' | <a href="' . $_SERVER['REQUEST_URI'] . '?explain=1">Explain</a>';
			}
			
			$this->view['debug_output'] = $debug_output;
		}
				
		header('Content-type: text/html; charset=utf-8');		
	}
	
	/**
	* LayoutController::set_title()
	* Set the page title. Should be called before page().
	* 
	* @param string $title Page title
	* @return bool success
	*/
	public function set_title($title) {
		if (empty($title)) {
			return false;
		}
		
		$this->view['title'] = $title;
		return true;
	}
	
	/**
	* LayoutController::get_styles()
	* Returns the style sheet tags to be used on the page.
	* 
	* @return string style tags
	*/
	private function get_styles() {
		$styles = array(
			'views/system/form.css',
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
	* @todo back to another namespace with you ;)
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
		$nodes = array($node->get_parent(7200));

		if ($nodes[0]->node_id) {
			$newnode = $nodes[0];
			$nodes = array_merge($nodes, $this->_get_parents($newnode));
		}
		
		return $nodes;
	}
	
	/**
	* LayoutController::get_nav()
	* Creates navigational links for a specified level.
	* 
	* @param integer $level Level of depth.
	* @return string navigation list
	*/
	private function get_nav($level = 1) {
		$active = array();
		
		if ($level == 1) {
			$node = cms::$vars['sitenode'];
			$nodes = $node->get_children(7200);
		} else if ($level > 1) {
			if (isset($this->parents[$level])) {
				$nodes = $this->parents[$level]->get_siblings_all(7200);
			} else if (isset($this->parents[$level - 1])) {
				$nodes = $this->parents[$level - 1]->get_children(7200);
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
			if ($node->node_id == (string) cms::$vars['sitenode']->options['homepage']) {
				$link = manager::base();
			} else {
				$link = $this->view->url('node/show/' . $node->node_id);
			}
		
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
