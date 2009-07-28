<?php
class extension_translation {
	public $tsetroot;
	private $got_from_url = false;
	private $current_url; // that's useful by hooking into the router :p
	
	public function get_url_language($url) {
		if (substr($url->query, 2, 1) == '/') {
			$possible_lcid = substr($url->query, 0, 2);
			
			$this->start_language($possible_lcid);
			
			$url->query = substr($url->query, 3);
			
			if ($url->query == '') {
				$url->query = 'node';
			}
			
			$this->current_url = $url->query;
			
			$this->got_from_url = $possible_lcid;
		}
	}
	
	public function set_url_language($url) {
		if ($this->got_from_url) {
			$url->url = $this->language . '/' . $url->url;
		}
	}
	
	public function homepage_language_link($link) {
		if ($this->got_from_url) {
			if ($link->data['link'] == cms::base()) {
				$link->data['link'] .= $this->language . '/';
			}
		}
	}
	
	public function __construct() {
		$this->init_translations();
		
		cms::$registry->register_type('TranslationSetNode');
		
		VEvents::register('node.hook-read', array($this, 'apply_translation'));
		VEvents::register('node.check-allowed', array($this, 'disable_tset'));
		VEvents::register('acp.node-edit-context-hook', array($this, 'set_acp_menu'));
		VEvents::register('acp.get-context-toolbars', array($this, 'translation_start'));
		VEvents::register('acp.get-context-toolbars', array($this, 'translation_settings'));
		VEvents::register('acp.get-node-widgets', array($this, 'translation_list_widget'));
		VEvents::register('core.alter-tree-title', array($this, 'widget_title'));
		VEvents::register('core.alter-menu', array($this, 'homepage_language_link'));
		VEvents::register('url.preroute-url', array($this, 'get_url_language'));
		VEvents::register('url.alter-output', array($this, 'set_url_language'));
		
		$node = new Node();
		$node->parent = 0;
		$node->type = 'translationset';
		$node->read(true);
		
		if (empty($node->title)) {
			$node = Node::create('Node');
			$node->parent = 0;
			$node->type = 'translationset';
			$node->title = 'Translations';
			$node->write();
		}

		$this->tsetroot = $node;
		
		if (isset($_GET['test_tset'])) {
			$tset = Node::create('Node');
			$tset->title = 'Home.translations';
			$tset->type = 'translationset';
			$tset->parent = $this->tsetroot->node_id;
			$tset->options['tset_parent'] = 2;
			$tset->write();
			$tset_id = $tset->node_id;
			
			$home = new Node();
			$home->node_id = 2;
			$home->read(true);
			$home->options['tset_set'] = $tset_id;
			$home->write();
		}
		
		if (isset($_GET['test_tsetw'])) {
			$test = Node::create('Node');
			$test->title = 'Huis';
			$test->description = 'Huispagina';
			$test->revision->revision_content = 'De huispagina van dit huis :-)';
			$test->parent = 7;
			$test->options['tset_language'] = 'nl';
			$test->type = 'page';
			$test->write();
		}
	}
	
	public function translation_list_widget($node) {
		$widgets = array();
		$has_tset = false;
		
		if (isset($node->options['tset_set'])) {
			$tset = Node::open((string)$node->options['tset_set']);
			$basenode = $node;
			
			$has_tset = true;
		} else { // check if the parent is a tset
			$tset = $node->get_parent();
		
			if (isset($tset->options['tset_parent'])) {
				$has_tset = true;
				$basenode = Node::open((string)$tset->options['tset_parent']);
			}
		}
		
		if (!$has_tset) {
			return array(); // we don't have a tset
		}
		
		// to get the selected link
		list($context, $cobject) = AdminController::get_context();
		
		if ($context == 'node_translate') {
			$selected = $cobject->curtrans->node_id;
		}
		
		$tree_options = array(
			'node' => $tset,
			'url' => array($this, 'widget_get_url'),
			'url_from' => 'admin',
			'selected' => $selected
		);
		
		$output = '<ul class="treeview">';
		$output .= cms::$helpers->get_tree($tree_options);
		$output .= '</ul>';
		
		$widgets['a_tset_list'] = array(
			'title' => __('Translations'),
			'content' => $output
		);
		
		return $widgets;
	}
	
	public function show_language_select() {
		// TODO: change this when the final language configuration tool is finished
		$options = new Node_Option();
		$options->option_name = 'tset_language';
		$options = $options->read();
		
		$languages = array();
		
		foreach ($options as $option) {
			$lcid = $option->option_value;
			
			if (in_array($lcid, $languages)) {
				continue;
			}
			
			$languages[] = $lcid;
		}
		
		$languages[] = 'en';
		
		$out = '';
		
		foreach ($languages as $lcid) {
			$out .= '<a href="' . view::url($lcid . '/' . $this->current_url, '', true) . '">';
			$out .= '<img src="' . cms::base() . 'extensions/translation/langicons/' . $lcid . '.png' . '" alt="' . $lcid . '" /> ';
			$out .= '</a>';
		}
		
		return $out;
	}
	
	public function widget_get_url($node) {
		if (isset($node->options['tset_parent'])) {
			return 'admin/controller/node/edit/' . (string)$node->options['tset_parent'];
		}
		
		return $node->to_admin_url();
	}
	
	public function widget_title($from, $title, $node) {
		// remove '.translations'
		if (string::ends_with($title->title, '.translations')) {
			$title->title = str_replace('.translations', '', $title->title);
		}
		
		// add the language to the title
		if (isset($node->options['tset_language'])) {
			$title->title .= ' (' . $node->options['tset_language'] . ')';
		}
	}
	
	public function set_acp_menu($node) {
		$parent = $node->get_parent();
		
		if (!isset($parent->options['tset_parent'])) {
			return;
		}
		
		$basenode = Node::open((string)$parent->options['tset_parent']);
		
		$basenode->curtrans = $node;
		AdminController::set_context('node_translate', $basenode);
	}
	
	public function create_translationset($node) {
		if (isset($node->options['tset_set'])) {
			$rnode = new Node();
			$rnode->node_id = (string)$node->options['tset_set'];
			$ret = $rnode->read();
			
			return $ret[0];
		}
		
		$tset = Node::create('Node');
		$tset->title = $node->title . '.translations';
		$tset->type = 'translationset';
		$tset->parent = $this->tsetroot->node_id;
		$tset->options['tset_parent'] = $node->node_id;
		$tset->write();
		$tset_id = $tset->node_id;
			
		$node->options['tset_set'] = $tset_id;
		$node->write();
		
		return $tset;
	}
	
	public function translation_start($context) {
		if ($context[0] == 'node') {
			if (!$context[1]->has_revision) {
				return;
			}
			
			return array(
				__('Translate') => array(
					// TODO: make custom icon
					'icon' => manager::base() . 'blueprint/views/admin/images/icons/upload.png',
					'callback' => 'admin/controller/translate/node/' . $context[1]->node_id,
				)
			);
		}
		
		if ($context[0] == 'node_translate') {
			$basenode = $context[1];
			
			return array(
				__('Return') => array(
					'icon' => manager::base() . 'blueprint/views/admin/images/icons/upload.png',
					'callback' => $basenode->to_admin_url(),
				),
				__('Translation list') => array(
					'icon' => manager::base() . 'blueprint/views/admin/images/icons/upload.png',
					'callback' => 'admin/controller/translate/node/' . $context[1]->node_id,
				),
			);
		}
	}
	
	public function translation_settings($context) {
		
	}
	
	private function init_translations() {
		// TODO: relocate translations
		$languages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
		
		if (isset($_COOKIE['viennacms2_tset'])) {
			$languages[0] = $_COOKIE['viennacms2_tset'];
			
			$this->got_from_url = $languages[0];
		}
			
		foreach ($languages as $language) {
			$lang = substr($language, 0, 2);
				
			if (file_exists(ROOT_PATH . 'locale/' . $lang) && is_dir(ROOT_PATH . 'locale/' . $lang)) {
				$this->language = $lang;
				_setlocale(LC_ALL, $lang);
				_bindtextdomain('viennacms2', ROOT_PATH . 'locale/');
				_textdomain('viennacms2');
				break;
			}
		}
		
		if (empty($this->language)) {
			$this->language = substr($languages[0], 0, 2);
		}
		
		if ($this->language == 'en') {
			_textdomain('dummy'); // to disable textdomains
		}
	}
	
	private function start_language($lcid) {
		$this->language = $lcid;
		
		if ($this->language == 'en') {
			_textdomain('dummy'); // to disable textdomains
		}
	}
	
	public function apply_translation($node) {
		if (!$node->open_readonly) {
			return;
		}
		
		if (((string)$node->options['tset_set']) == 0) {
			return;
		}
		
		$query = new Node();
		$query->parent = (string)$node->options['tset_set'];
		$query->type = $node->type;
		$query->cache = 3600;
		$results = $query->read();
		$translation = null;
		
		foreach ($results as $result) {
			if (((string)$result->options['tset_language']) == $this->language) {
				$translation = $result;
				break;
			}
		}
		
		if (!$translation) {
			return;
		}
		
		$translation->node_id = $node->node_id;
		$translation->open_readonly = true;
		$translation->parent = $node->parent;
		
		Model::copy_data($translation, $node);
	}
	
	function disable_tset($type, $node, $other) {
		switch ($type) {
			case 'this_under_other':
				if ($node->type == 'translationset') {
					return false;
				}
				
				return true;
			break;
			case 'show_in_tree':
				if ($node->type == 'translationset') {
					return false;
				}
				
				return true; 
			break;
		}
	}
}
?>