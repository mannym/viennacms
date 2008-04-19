<?php
/**
* Page class for viennaCMS.
* "Let's print out that page, and use that layout"
* 
* @package viennaCMS
* @author viennacms.nl
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
	* 
	* @param bool init: Do we need to initialize the page?
	* @return instance $instance
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
		$template = template::getnew();
		
		// first get the site, the decoder may need it :)
		$this->sitenode = $this->get_this_site();

		$template->assign_vars(array(
			'left' => $this->get_bloc('left'),
			'right' => $this->get_bloc('right')
		));
		
		// first try to decode an URL.
		$parser = $this->try_decode_url();
		if (!$parser) {
			$this->show_node(array('node_id' => $this->sitenode->node_id));
		}
		
		if ($parser === '404') {
			header('HTTP/1.0 404 Not Found', true, 404);
			$this->init_page($this->sitenode);
			$template->assign_vars(array(
				'title' => __('Page not found'),
				'sitename' => $this->sitenode->title,
				'sitedescription' => $this->sitenode->description,
				'middle' => __('The page you requested can not be found'),
			));
		}
		
		$template->vars['middle'] = $this->get_bloc('before_content') . $template->vars['middle'] . $this->get_bloc('after_content');
		$template->vars['content'] = &$template->vars['middle'];

		// and then run
		/*$id = intval($_GET['id']);
		
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
		$this->get_template();*/
	}
	
	/**
	* Show a node... 
	* 
	* @param array $args: The args, like node id etc.
	*/

	public function show_node($args) {
		global $Header;
		
		$id = intval($args['node_id']);
		
		$node = new CMS_Node();
		
		if (!$id) {
			$node = $this->sitenode;
		} else {
			$node->node_id = $id;
			$node->read();
		}
		$user = user::getnew();
		if (isset($args['revision'])) {
			$user->initialize(true);
			if ($user->user_logged_in) {
				$node->revision->revision_id = 0;
				$node->revision->revision_number = $args['matches'][1];
				$node->revision->read($node);
				
				$this->get_revision_nav($node);
			}
		}
		
		$this->node = $node;
		$this->init_page($this->node);
		
		$template = template::getnew();
		$template->assign_vars(array(
			//'right' => $this->get_loc('right'),
			'middle' => $this->get_loc('middle'),
			//'left' => $this->get_loc('left'),
			'title' => $this->node->title,
			'description' => $this->node->description,
			'sitename' => $this->sitenode->title,
			'sitedescription' => $this->sitenode->description,
			'crumbs' => $this->make_breadcrumbs()
		));
	}
	
	/**
	 * Initialize the page, good for getting template and parent nodes
	 * @param CMS_Node $node
	 */
	
	public function init_page(CMS_Node $node) {
		$this->parents = $this->get_parents($node);
		$this->get_template();
	}
	
	/**
	* Get revision navigation
	* 
	* @param CMS_Node $node
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
	* 
	* @param int $level: The level
	* @return $ret: navigation
	*/
	public function get_nav($level = 1) {
		utils::get_types();
	
		$active = array();
		
		if ($level == 1) {
			$node = $this->sitenode;
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
				
		$ret = '';
		
		if (!isset($nodes) || !$nodes) {
			return;
		}
		
		foreach ($nodes as $node) {
			$ext = utils::load_extension(utils::$types[$node->type]['extension']);
			$show = utils::display_allowed('show_to_visitor', $node);
			/*if (method_exists($ext, $node->type . '_show_to_visitor')) {
				$function = $node->type . '_show_to_visitor';
				$show = $ext->$function($node);
			}*/
		
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
	* 
	* @param CMS_Node $node
	* @param string $extra_params
	* @param $site_full_link: Do we need a full link for the site?
	* @return string $link
	*/

	function get_link($node, $extra_params = '', $site_full_link = false) {
		$link = '';
		$prefix = '';
		
		if ($node->type == 'file') {
			$prefix .= 'file-download/';
		}
	
		if ($node->type == 'site' && !$site_full_link) {
			$link .= utils::basepath();
			$link .= substr($extra_params, 1);
			$link .= ( (empty($node->extension) ? '' : '.' . $node->extension));
		} else if ($this->sitenode->options['rewrite'] || ($this->sitenode->options['rewrite'] && $site_full_link)) {
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
	* 
	* @return string $crumbs
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
	* 
	* @param CMS_Node $node
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
	 * @param CMS_Node $node
	 */
	
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
	* Get the current site node, and initialize language and style.
	* 
	* @return mixed $this_site: The current site
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

		if (isset($this_site->options['blocks'])) {
			$this_site->options['blocks'] = unserialize($this_site->options['blocks']);
		} else {
			$this_site->options['blocks'] = array();
		}
		
		// Translation is looking for in ./locale/$language/LC_MESSAGES/viennacms.mo now
		return $this_site;
	}
	
	/**
	* Gets a module location. 
	* 
	* @param string $location
	* @return mixed $return
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
				$module['location'] = $location;
				
				ob_start();
				$mret = $ext->$module_function($module);
				$contents = ob_get_contents();
				ob_end_clean();

				if ($mret != 500) {
					$template->set_alt_filename($module_function, array('module-' . $location . '.php', 'module.php'));
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

	/**
	* Gets a block location
	* 
	* @param string $location
	* @return mixed $return 
	*/
	public function get_bloc($location) {
		$return = '';
		if (isset($this->prefix[$location])) {
			$return .= $this->prefix[$location];
		}
		
		$template = template::getnew();
		if(!isset($this->sitenode->options['blocks'][$location]))
		{
			$this->sitenode->options['blocks'][$location] = array();
		}
		foreach ($this->sitenode->options['blocks'][$location] as $module) {
			$module_function = 'module_' . $module['module'];
			$ext = utils::load_extension($module['extension']);
			$module['location'] = $location;
			
			ob_start();
			$mret = $ext->$module_function($module);
			$contents = ob_get_contents();
			ob_end_clean();

			if ($mret != 500) {
				$template->set_alt_filename($module_function, array('module-' . $location . '.php', 'module.php'));
				$template->assign_priv_vars($module_function, array(
					'title' 	=> htmlentities($module['content_title']),
					'content' 	=> $contents,
					'margin'  	=> ( $location == 'middle' ? ' style="margin-left: 20px;"' : ''),
				));
				$return .= $template->assign_display($module_function);
			} else {
				$return .= $contents;
			}
		}
		return $return;
	}
	
	/**
	 * Decode the current url
	 * 
	 * @return do_decode
	 */
	
	public function try_decode_url() {
		$uri_no_qs = explode('?', $_SERVER['REQUEST_URI']);
		$uri_no_qs = $uri_no_qs[0];
		
		if (strpos($_SERVER['REQUEST_URI'], '.php') === false
			&& $uri_no_qs != utils::basepath(true)) {
			$uri = '/' . preg_replace('@^' . preg_quote(utils::basepath(), '@') . '@', '', $uri_no_qs);
		} else if (isset($_SERVER['PATH_INFO']) && !empty($_SERVER['PATH_INFO'])) {
			$uri = $_SERVER['PATH_INFO'];
		} else {
			return false;
		}
		
		return $this->do_decode($uri);
	}
	
	/**
	 * Refgex fix
	 * @param string $url
	 * @return string $url: The fixed url.
	 */
	
	private function regexify($url) {
		$url = '@^/' . $url . '$@';
		$url = str_replace(array('%text', '%number'), array('([a-z\-0-9]+)', '([0-9]+)'), $url);
		return $url;
	}
	
	/**
	 * Do the decode
	 * 
	 * @param string $uri
	 * @return mixed succes
	 */
	
	private function do_decode($uri) {
		global $cache;
		$uri = urldecode($uri); // for spaces and other characters in URIs
				
		$sitehash = md5($this->sitenode->options['hostname']);
		if (!($urls = $cache->get('_url_callbacks_' . $sitehash))) {
			$urls = utils::run_hook_all('url_callbacks');
			$cache->put('_url_callbacks_' . $sitehash, $urls);			
		}
	
		uksort($urls, array('utils', 'lsort_callback'));
		$found = false;
		foreach ($urls as $url => $data) {
			$url = $this->regexify($url);
			if (preg_match($url, $uri, $matches)) {
				$data['parameters']['matches'] = $matches;
				$found = true;
				break;
			}
		}
		
		if (!$found) {
			return '404';
		}
		
		switch ($data['cbtype']) {
			case 'create_new_getnew':
				if ($data['callback'][0] == __CLASS__) {
					$callback = array(&$this, $data['callback'][1]);
				} else {
					// not implemented
				}
			break;
			case 'create_new':
				$class = new $data['callback'][0];
				$callback = array($class, $data['callback'][1]);
			break;
		}
		
		call_user_func($callback, $data['parameters']);
		return true;
	}
	
	/**
	 * Get the correct link
	 * 
	 * @param array $matches
	 * @return string $link
	 */
	
	public function get_correct_link($matches) {
		global $pages, $fixed_url;
		$node = new CMS_Node();
		$node->node_id = $matches[1];
		$node->read();
		
		$link = $this->get_link($node);
		$link = 'href="' . $link . '"';
		
		//$replacement = str_replace('{node:' . $matches[1] . '}', $link, $matches[0]);
		
		return $link;
	}
}
?>
