<?php
/**
* Page class for viennaCMS.
* "Let's print out that page, and use that layout"
* 
* @package viennaCMS
* @author viennainfo.nl
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

/**
* @ignore
*/

if (!defined('IN_VIENNACMS')) {
	exit;
}

/**
* Page generation class. Contains most methods to generate a page :)
* 
* @package viennaCMS
*/
class page {
	static $instance;
	private $found_template;	
	public $node;
	public $sitenode;
	public $parents;
	public $prefix;
	
	/**
	* Return an instance of page.
	*/
	static function getnew($init = true) {
		if (!isset(self::$instance)) {
			self::$instance = new page;
			if ($init) {
				if (method_exists(self::$instance, 'initialize'))
				{
					self::$instance->initialize();
				}
			}
		}
		return self::$instance;
	}
	
	/**
	* Initialize the page class. 
	*/
	public function initialize() {
		// first try to decode an URL.
		$this->try_decode_url();

		// and then run
		$id = intval($_GET['id']);
		$this->sitenode = $this->get_this_site();
		
		$node = new CMS_Node();
		
		if (!$id) {
			$node = $this->sitenode;
		} else {
			$node->node_id = $id;
			$node->read();
		}
		$user = user::getnew();
		if (isset($_GET['revision'])) {
			$user->initialize(true);
			if ($user->user_logged_in) {
				$node->revision->revision_id = 0;
				$node->revision->revision_number = $_GET['revision'];
				$node->revision->read($node);
				
				$this->get_revision_nav($node);
			}
		}
		
		$this->node = $node;
		$this->parents = $this->get_parents($this->node);
		$this->get_template();
	}
	
	/**
	* Get revision navigation
	*/
	
	private function get_revision_nav($node) {
		$db = database::getnew();
	
		$html = sprintf(__('You are currently viewing revision %d of this node, which was made on %s.'), $node->revision->revision_number, date('d-m-Y G:i:s', $node->revision->revision_date));
		$next = $node->revision->revision_number + 1;
		$prev = $next - 2;
		$nextfound = $prevfound = false;
		
		$sql = 'SELECT * FROM ' . NODE_REVISIONS_TABLE . ' WHERE node_id = ' . $node->node_id . ' AND (revision_number = ' . $next . ' OR revision_number = ' . $prev . ') ORDER BY revision_date DESC';
		$result = $db->sql_query($sql);
		$rowset = $db->sql_fetchrowset($result);
		
		//var_dump($rowset);
		//var_dump($next);
		//var_dump($prev);
		
		foreach ($rowset as $row) {
			if ($row['revision_number'] == $next) {
				$nextrow = $row;
				$nextfound = true;
			} else if ($row['revision_number'] == $prev) {
				$prevrow = $row;
				$prevfound = true;
			}
		}
		
		$html .= '<br /><br />';
		
		if ($prevfound) {
			$html .= '<div style="width: 49%; float: left;">';
			$html .= '<a href="' . $this->get_link($node, '/revision/' . $prevrow['revision_number']) . '">';
			$html .= sprintf(__('&laquo; Previous (revision %d)'), $prevrow['revision_number']);
			$html .= '</a></div>' . "\r\n";
		}
		
		if ($nextfound) {
			$html .= '<div style="width: 49%; float: right; text-align: right;">';
			$html .= '<a href="' . $this->get_link($node, '/revision/' . $nextrow['revision_number']) . '">';
			$html .= sprintf(__('Next (revision %d) &raquo;'), $nextrow['revision_number']);
			$html .= '</a></div>' . "\r\n";
		}
		
		$html .= '<br style="clear: both;" />';
				
		$this->prefix['middle'] = $html;
	}
	
	/**
	* Get navigation on a selected level. 
	*/
	public function get_nav($level = 1) {
		utils::get_types();
	
		$active = array();
		
		if ($level == 1) {
			$node = $this->sitenode;
			$nodes = $node->get_children();
		} else if ($level > 1) {
			if ($this->parents[$level]) {
				$nodes = $this->parents[$level]->get_siblings_all();
			} else if ($this->parents[$level - 1]) {
				$nodes = $this->parents[$level - 1]->get_children();
			}
		}
		foreach ($this->parents as $parent) {
			$active[] = $parent->node_id;
		}
				
		$ret = '';
		
		if (!$nodes) {
			return;
		}
		
		foreach ($nodes as $node) {
			$ext = utils::load_extension(utils::$types[$node->type]['extension']);
			$show = true;
			if (method_exists($ext, $node->type . '_show_to_visitor')) {
				$function = $node->type . '_show_to_visitor';
				$show = $ext->$function($node);
			}
		
			if ($show) {
				$class = '';
				
				if (in_array($node->node_id, $active)) {
					$class = ' class="active"';
				}
				
				$ret .= "						<li$class>\r\n";
				$ret .= '							<a href="' . $this->get_link($node) . '">';
				$ret .= $node->title;
				$ret .= "</a>\r\n";
				$ret .= "						</li>\r\n";
			}
		}
		
		return $ret;
	}
	
	/**
	* Assigns most used navigation levels to a template. 
	*/

	function assign_nav() {
		$template = template::getnew();
		$template->assign_vars(array(
			'nav_level1' => $this->get_nav(1),
			'nav_level2' => $this->get_nav(2),
			'nav_level3' => $this->get_nav(3),
			'nav_level4' => $this->get_nav(4),
		));
	}
	
	/**
	* Get a link to a node. 
	*/

	function get_link($node, $extra_params = '') {
		$link = '';
		$prefix = '';
		
		if ($node->type == 'file') {
			$prefix .= 'file-download/';
		}
	
		if ($node->type == 'site') {
			$link .= utils::basepath();
			$link .= substr($extra_params, 1);
			$link .= ( (empty($node->extension) ? '' : '.' . $node->extension));
		} else if ($this->sitenode->options['rewrite']) {
			//$link = $extra_params;
			$link .= $prefix . (empty($node->parentdir) ? '' :  $node->parentdir . '/') . $node->title_clean . $extra_params . ( (empty($node->extension) ? '' : '.' . $node->extension));
		} else {
			//$link = 'index.php?id=' .  $node->node_id;
			$link .= 'index.php/' . $prefix;
			$link .= (empty($node->parentdir) ? '' :  $node->parentdir . '/') . $node->title_clean . $extra_params . ( (empty($node->extension) ? '' : '.' . $node->extension));
		}
		return $link;
	}
	
	/**
	* Generates breadcrumbs. 
	*/

	function make_breadcrumbs() {
		$crumbs = '';
		
		foreach ($this->parents as $node) {
			$newcrumb  = ' &laquo; <a href="' .  $this->get_link($node) . '">';
			$newcrumb .= $node->title;
			$newcrumb .= '</a>';  

			$crumbs = $crumbs . $newcrumb;
		}

		$crumbs = substr($crumbs, 9);
		
		return $crumbs;
	}
	
	/**
	* Get all parents of a node. 
	*/

	public function get_parents($node) {
		$array = array($node);
		$array = array_merge($array, $this->_get_parents($node));
		$array = array_reverse($array);
		return $array;
	}
	
	private function _get_parents($node) {
		$nodes = $node->get_parent();

		if ($nodes) {
			$newnode = $nodes[0];
			$nodes = array_merge($nodes, $this->_get_parents($newnode));
		}
		
		return $nodes;
	}
	
	/**
	* Get the current node template. 
	*/

	public function get_template() {
		$parents = array_reverse($this->parents);
		foreach ($parents as $node) {
			if (!empty($node->options['template'])) {
				$template = template::getnew();
				$template->initialize($node->options['template']);
				return;
			}
		}
	}
	
	/**
	* Get the current site node.
	*/
	public function get_this_site() {
		$node = new CMS_Node();
		
		$node->node_id = 0;
		$sites = $node->get_children();
		$this_site = false;
		
		foreach ($sites as $site) {
			if ($site->type != 'site') {
				continue;
			}
			
			if ($site->options['hostname'] == $_SERVER['HTTP_HOST'] ||  empty($site->options['hostname']))  {
				$this_site = $site;
				break;
			}
		}
		
		if ($this_site === false) {
			foreach ($sites as $site) {
				if (empty($site->options['hostname'])) {
					$this_site = $site;
					break;
				}
			}
		}
		
		if ($this_site === false) { // gracefully die
			trigger_error(__('No site node found.'), E_USER_ERROR);
		}
		
		// Now set the language
		
		$language = 'en_EN'; // Default language
		
		if(!empty($this_site->options['language'])) {
			$language = $this_site->options['language'];
		}
		// Set language to $language
		_setlocale(LC_ALL, $language);
	
		// Specify location of translation tables
		_bindtextdomain("viennacms", ROOT_PATH . "locale");
	
		// Choose domain
		_textdomain("viennacms");

		// Translation is looking for in ./locale/$language/LC_MESSAGES/viennacms.mo now
		return $this_site;
	}
	
	/**
	* Gets a module location. 
	*/
	public function get_loc($location) {
		$return = '';
		if (isset($this->prefix[$location])) {
			$return .= $this->prefix[$location];
		}
		
		if ($this->node->revision->has_modules) {
			$template = template::getnew();
			foreach ($this->node->revision->modules[$location] as $module) {
				$module_function = 'module_' . $module['module'];
				$ext = utils::load_extension($module['extension']);
				
				ob_start();
				$mret = $ext->$module_function($module);
				$contents = ob_get_contents();
				ob_end_clean();

				if ($mret != 500) {
					$template->set_filename($module_function, 'module.php');
					$template->assign_vars(array(
						'title' 	=> htmlentities($module['content_title']),
						'content' 	=> $contents,
						'margin'  	=> ( $location == 'middle' ? ' style="margin-left: 20px;"' : ''),
					));
					$return .= $template->assign_display($module_function);
				} else {
					$return .= $contents;
				}
			}
		} else {
			if ($location == 'middle') {
				$return .= utils::handle_text($this->node->revision->node_content);
			}
		}
		return $return;
	}

	public function try_decode_url() {
		$uri_no_qs = explode('?', $_SERVER['REQUEST_URI']);
		$uri_no_qs = $uri_no_qs[0];
		
		if (strpos($_SERVER['REQUEST_URI'], '.php') === false
			&& $uri_no_qs != utils::basepath(true)) {
			$uri = '/' . preg_replace('@^' . preg_quote(utils::basepath(), '@') . '@', '', $uri_no_qs);
		} else if (isset($_SERVER['PATH_INFO']) && !empty($_SERVER['PATH_INFO'])) {
			$uri = $_SERVER['PATH_INFO'];
		} else {
			return;
		}
		
		$this->do_decode($uri);
	}
	
	private function do_decode($uri) {
		if (!preg_match('/(.*\/)(.+?)(\..+)*$/', $uri, $uri_parts)) {
			return;
		}

		$uri_noext = $uri_parts[1] . $uri_parts[2];
		$parsers = utils::run_hook_all('url_parsers');
		$parsers['@/revision/([0-9]+)$@'] = array(1 => 'revision');
		foreach ($parsers as $parser => $destination) {
			if (preg_match($parser, $uri_noext, $parse_regs)) {
				$uri_noext = preg_replace($parser, '', $uri_noext);
				foreach ($destination as $reg => $param) {
					$_GET[$param] = $parse_regs[$reg];
				}
			}
		}

		$uri = $uri_noext . $uri_parts[3];
		if (!preg_match('/(.*\/)(.+?)(\..+)*$/', $uri, $uri_parts)) {
			return;
		}
		
		$parsers = utils::run_hook_all('url_full_parsers');
		foreach ($parsers as $parser => $destination) {
			if (preg_match($parser, $uri, $parse_regs)) {
				$uri = preg_replace($parser, '', $uri);
				foreach ($destination as $reg => $param) {
					$_GET[$param] = $parse_regs[$reg];
				}
			}
		}		

		if (!preg_match('/(.*\/)(.+?)(\..+)*$/', $uri, $uri_parts)) {
			return;
		}
		
		// get revision parameter
/*		if (preg_match('#revision/([0-9]+)#', $uri_parts[1], $regs)) {
			$uri_parts[1] = str_replace($regs[0], '', $uri_parts[1]);
			$_GET['revision'] = $regs[1];
		}*/
		
		// Parent dir
		if(empty($uri_parts[1])) {
			$parentdir = '';
		}
		else {
			$parentdir = substr($uri_parts[1], 1, strlen($uri_parts[1]) - 2);
		}
		// extension
		if(empty($uri_parts[3])) {
			$extension = '';
		}
		else {
			$extension = substr($uri_parts[3], 1);
		}
				
		$node = new CMS_Node();
		$node->title_clean	= $uri_parts[2];
		$node->parentdir	= $parentdir;	
		$node->extension 	= $extension;
		$node->read(NODE_TITLE);
		
		$_GET['id'] = $node->node_id;
	}
	
	public function get_correct_link($matches) {
		global $pages, $fixed_url;
		$node = new CMS_Node();
		$node->node_id = $matches[1];
		$node->read();
		
		$link = $this->get_link($node);
		
		$replacement = str_replace('{node:' . $matches[1] . '}', $link, $matches[0]);
		
		return $replacement;
	}
}
?>
