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
	
	public $rawnav;
	
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
		
		$left_suffix = (!empty($this->left)) ? $this->left : '';
		$right_suffix = (!empty($this->right)) ? $this->right : '';
		
		$template->assign_vars(array(
			'left' => $this->get_bloc('left') . $left_suffix,
			'right' => $this->get_bloc('right') . $right_suffix
		));
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
			$node->read(NODE_SINGLE, true);
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
		
		$this->left = $this->get_loc('left');
		$this->right = $this->get_loc('right');
		
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
		$this->assign_rawnav();
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
	* Assign raw navigation for this site.
	*/
	
	public function assign_rawnav() {
		$this->rawnav = $this->_assign_rawnav($this->sitenode, array());
	}
	
	private function _assign_rawnav($node, $links) {
		$nodes = $node->get_children(true);
		$sublinks = array();
		
		foreach ($nodes as $child) {
			$new = $this->_assign_rawnav($child, $links);
			if (!empty($new)) {
				$sublinks = array_merge($sublinks, $new);
			}		
		}
		
		$show = utils::display_allowed('show_to_visitor', $node);
		
		if ($show && $node->node_id != $this->sitenode->node_id) {
			$links['n' . $node->node_id] = array(
				'href'			=> $this->get_link($node),
				'text'			=> $node->title,
				'description'	=> $node->description,
				'children'		=> $sublinks,
			);
		} else if ($node->node_id == $this->sitenode->node_id) {
			$links = $sublinks;
		}
		
		return $links;
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
			$nodes = $node->get_children(true);
		} else if ($level > 1) {
			if (isset($this->parents[$level])) {
				$nodes = $this->parents[$level]->get_siblings_all();
			} else if (isset($this->parents[$level - 1])) {
				$nodes = $this->parents[$level - 1]->get_children(true);
			}
		}
		foreach ($this->parents as $parent) {
			$active[] = $parent->node_id;
		}
				
		$links = array();
		
		if (!isset($nodes) || !$nodes) {
			return;
		}
		
		foreach ($nodes as $node) {
			$show = utils::display_allowed('show_to_visitor', $node);
		
			if ($show) {
				$class = '';
				
				if (in_array($node->node_id, $active)) {
					$class = 'active';
				}
				
				$links['n' . $node->node_id] = array(
					'class'			=> $class,
					'href'			=> $this->get_link($node),
					'text'			=> $node->title,
					'description'	=> $node->description
				);
			}
		}
		
		return theme('links', 'nav_level' . $level, $links);
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
			//$link .= utils::basepath();
			$link .= substr($extra_params, 1);
			$link .= ( (empty($node->extension) ? '' : '.' . $node->extension));
			if (empty($extra_params)) {
				$link = utils::url($link, array('nonsys_url' => true));
			} else {
				$link = utils::url($link);
			}
		} /*else if ($this->sitenode->options['rewrite'] || ($this->sitenode->options['rewrite'] && $site_full_link)) {
			//$link = $extra_params;
			$link .= $prefix . (empty($node->parentdir) ? '' :  $node->parentdir . '/') . $node->title_clean . $extra_params . ( (empty($node->extension) ? '' : '.' . $node->extension));
		} else {
			//$link = 'index.php?id=' .  $node->node_id;
			$link .= 'index.php/' . $prefix;
			$link .= (empty($node->parentdir) ? '' :  $node->parentdir . '/') . $node->title_clean . $extra_params . ( (empty($node->extension) ? '' : '.' . $node->extension));
		}*/
		else {
			//$link = 'index.php?id=' .  $node->node_id;
			$link .= $prefix;
			$link .= (empty($node->parentdir) ? '' :  $node->parentdir . '/') . $node->title_clean . $extra_params . ( (empty($node->extension) ? '' : '.' . $node->extension));
			$link = utils::url($link);
		}
		
		return $link;
	}
	
	/**
	* Generates breadcrumbs. 
	* 
	* @return string $crumbs
	*/

	function make_breadcrumbs() {
		return theme('breadcrumbs', $this->parents);
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
		$sites = $node->get_children(true);
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
		$return = '<div class="location-' . $location . ' location">';
		$return .= $this->get_loc_real($location);
		$return .= '</div>';
		
		return $return;
	}
	
	public function get_loc_real($location) {
		$return = '';
		if (isset($this->prefix[$location])) {
			$return .= $this->prefix[$location];
		}
		
		if ($this->node->revision->has_modules) {
			foreach ($this->node->revision->modules[$location] as $key => $module) {
				$return .= $this->get_module($module, $location, $key);
			}
		} else {
			if ($location == 'middle') {
				$return .= utils::handle_text($this->node->revision->node_content);
			}
		}
		return $return;
	}
	
	function get_module($module, $location, $key) {
		$template = template::getnew();
		
		$return = '';
		$module_function = 'module_' . $module['module'];
		$ext = utils::load_extension($module['extension']);
		$module['location'] = $location;
		
		ob_start();
		$mret = $ext->$module_function($module);
		$contents = ob_get_contents();
		ob_end_clean();
		
		$module_function = $module_function . '-' . $key . '-' . $location . '-' . $this->node->node_id;

		$contents = '<div class="module-c" id="' . $module_function . '">' . $contents . '</div>';

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
		
		return $return;
	}

	/**
	* Gets a block location
	* 
	* @param string $location
	* @return mixed $return 
	*/
	public function get_bloc($location) {
		$return = '<div class="location-' . $location . '">';
		$return .= $this->get_bloc_real($location);
		$return .= '</div>';
		
		return $return;
	}
	
	public function get_bloc_real($location) {
		$return = '';
		if (isset($this->prefix[$location])) {
			$return .= $this->prefix[$location];
		}
		
		$template = template::getnew();
		if(!isset($this->sitenode->options['blocks'][$location]))
		{
			$this->sitenode->options['blocks'][$location] = array();
		}
		foreach ($this->sitenode->options['blocks'][$location] as $key => $module) {
			$module_function = 'module_' . $module['module'];
			$ext = utils::load_extension($module['extension']);
			$module['location'] = $location;
			
			ob_start();
			$mret = $ext->$module_function($module);
			$contents = ob_get_contents();
			ob_end_clean();

			$module_function = 'block' . $module_function . $location . $key;

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

		global $config;
		if ($config['caching_type'] == 'normal' || $config['caching_type'] == 'aggressive') {
			$pagehash = sha1(serialize($data));
			$pages = $cache->get('_page_output');
			if (isset($pages[$pagehash])) {
				if ($pages[$pagehash]['expire'] > time()) {
					echo base64_decode($pages[$pagehash]['output']);
					exit;
				}
			}
		
			$this->pagehash = $pagehash;
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
		
		$result = call_user_func($callback, $data['parameters']);
		
		if ($result == 500) {
			exit;
		}
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
