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
	public function __construct() {
		View::$searchpaths['layouts/' . cms::$vars['style'] . '/'] = VIEW_PRIORITY_USER;
	}
	
	/**
	* LayoutController::page()
	* Assigns variables to the page.php view, including the page content.
	* 
	* @param string $content Content of the page
	* @return void
	*/
	public function page($content) {
		global $starttime;


		// look if any extension wants to say something
		$data = new stdClass;
		$data->content = $content;
		$data->header = '';

		manager::run_hook_all('layout_pre_display', $data);

		$content = $data->content;
		$header = $data->header;

		// do some stuff for if the page is a node
		if (isset(cms::$vars['node'])) {
			// get parents and the description
			$this->parents = $this->get_parents(cms::$vars['node']);
			$this->view['description'] = cms::$vars['node']->description;
			// create some meta tags
			$header .= '<meta name="copyright" content="(c) 2008, 2009 viennaCMS developers" />' . "\r\n";
			$header .= '<meta name="description" content="' . cms::$vars['node']->description . '" />';
		} else {
			$this->parents = array();
		}

		// assign navigation
		$this->view['nav'] = array(
			1 => $this->get_nav(1),
			2 => $this->get_nav(2)
		);

		// some standard templating stuff
		$this->view['content'] = $content;
		$this->view['styles'] = $this->get_styles();
		$this->view['head'] = $header;

		$u_lilo = '';
		$l_lilo = '';

		// user links
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
		
		// Can the user use the ACP?
		$this->view['acp_auth'] = false;
		$auth = new VAuth();
		$rights = $auth->get_rights('admin:see_acp', cms::$user->user);
		
		if (in_array('y', $rights)) {
			$this->view['acp_auth'] = true;
		}

		$this->view['admin_link'] = '';

		if ($this->view['acp_auth']) {
			$this->view['admin_link'] = view::link('admin', __('Administration Control Panel'));
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
		
		$this->view['main_sidebar'] = $this->retrieve_sidebar('main_sidebar');
		$this->view['sec_sidebar'] = $this->retrieve_sidebar('secondary_sidebar');
				
		header('Content-type: text/html; charset=utf-8');		
	}
	
	public function retrieve_sidebar($location) {
		// okay, this is going to be annoying... but we'll try :)
		$return = manager::run_hook_all('retrieve_sidebar', $location);
		
		return implode("\n", $return);
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
			'framework/views/system/form.css',
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
		$links = array();
		
		foreach ($nodes as $node) {
			$text = $node->title;
			if ($node->node_id == (string) cms::$vars['sitenode']->options['homepage']) {
				$link = manager::base();
			} else {
				$link = $this->view->url('node/show/' . $node->node_id);
			}
		
			$class = '';
				
			if (in_array($node->node_id, $active)) {
				$class = 'active';
			}
			
			$links[] = array(
				'link' => $link,
				'class' => $class,
				'title' => $text,
				'description' => $node->description
			);
		}
		
		$view = new View();
		$view->path = array('style/links-nav-' . $level . '.php', 'style/links.php');
		$view['links'] = $links;
		$return .= $view->display();
		
		return $return;
	}
	
}
