<?php
/**
* News extension for viennaCMS
* 
* @package viennaCMS
* @author viennacms.nl
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*/

if (!defined('IN_VIENNACMS')) {
	exit;
}

class extension_news {
	function admin_init() {
		$css = <<<CSS
.nodes a.news { background: url(../extensions/news/news.png) 0 0 no-repeat; }
.nodes a.newsfolder { background: url(../extensions/news/folder.png) 0 0 no-repeat; }	
CSS;
		utils::add_css('inline', $css);
	}

	function list_modules() {
		return array(
			'latestnews' => 'news',
			'newsheadlines' => 'news'
		);
	}
	
	function list_types() {
		return array(
			'newsfolder' => array(
				'extension' => 'news',
				'type' => NODE_NO_REVISION,
				'allow_easy' => true,
				'description' => __('A news folder is a container which contains news items. If you want to place news items on your web sit, you need a news folder.'),
				'title' => __('News folder')
			),
			'news' => array(
				'extension' => 'news',
				'type' => NODE_CONTENT,
				'field' => 'wysiwyg',
				'allow_easy' => true,
				'description' => __('A news item is a small post to announce something new on your web site. These will be displayed on a page with the correct modules.'),
				'title' => __('News item')
			)
		);
	}
	
	function news_allow_as_child($node) {
		if ($node->type != 'newsfolder') {
			return false;
		}
		
		return true;
	}
	
	function newsfolder_allow_as_child($node) {
		if ($node->type != 'site') {
			return false;
		}
		
		return true;
	}
	
	function newsfolder_show_to_visitor($node) {
		return false;
	}
	
	function display_node($type, $node, $other = false) {
		switch ($type) {
			case 'this_under_other':
				if ($node->type == 'news') {
					if ($other->type == 'newsfolder') {
						return true;
					} else {
						return false;
					}
				}
				
				if ($node->type == 'newsfolder') {
					if ($other->type == 'site') {
						return true;
					} else {			
						return false;
					}
				}
			break;
			case 'other_under_this':
				if ($node->type == 'newsfolder') {
					if ($other->type != 'news') {
						return false;
					}
				}
				
				return true;
			break;
			case 'show_to_visitor':
				if ($node->type == 'newsfolder') {
					return false;
				}
				
				return true;
			break;
			default:
				return true;
			break;
		}
	}
	
	function args_latestnews() {
		$testnews = new CMS_Node();
		$testnews->type = 'news';
		
		return array(
			'folder' => array( // 'content' is the argument name
				'title' => __('News folder'), // title is what it will show
				'type' => 'node',
				'cbtype'	=> 1, 
				'newrow' => false, // true to make the control 100% width in ACP
				'callback' => array(
					'type'		=> 'this_under_other',
					'ntype'		=> 'other',
					'node'		=> $testnews
				)
			),
			'count' => array(
				'title' => __('Count of newsitems to display'),
				'type' => 'text',
				'newrow' => false,
			),
		);
		
		unset($testnews);
	}
	
	function args_newsheadlines() {
		return $this->args_latestnews();
	}
	
	function module_latestnews($args) {
		if (!$args['folder']) {
			echo __('Please select a news folder.');
			return;
		}
		
		if (!$args['count']) {
			$args['count'] = 10;
		}
		
		$folder = new CMS_Node();
		$folder->node_id = $args['folder'];
		$folder->read();
		$nodes = $folder->get_children();
		$news = array();
		
		foreach ($nodes as $node) {
			$news[$node->created] = $node;
		}
		
		krsort($news);
		
		$i = 1;
		
		foreach ($news as $new) {
			if ($i > $args['count']) {
				break;
			}
			
			$this->show_news($new, $args);

			$i++;
		}
		
		return 500;
	}
	
	function module_newsheadlines($args) {
		if (!$args['folder']) {
			echo __('Please select a news folder.');
			return;
		}
		
		if (!$args['count']) {
			$args['count'] = 10;
		}
		
		$folder = new CMS_Node();
		$folder->node_id = $args['folder'];
		$folder->read();
		$nodes = $folder->get_children();
		$news = array();
		
		foreach ($nodes as $node) {
			$news[$node->created] = $node;
		}
		
		krsort($news);
		
		$i = 1;
		
		$page = page::getnew(false);
		
		echo '<ul class="news-headlines">';
		
		foreach ($news as $new) {
			if ($i > $args['count']) {
				break;
			}
			
			echo '<li>' . '<a href="' . $page->get_link($new) . '">' . $new->title . '</a>' . '</li>';

			$i++;
		}
		
		echo '</ul>';
	}
	
	function show_news($node, $args) {
		$date = $node->created;
	
		$template = template::getnew();
		$page = page::getnew(false);
	
		$content = nl2br(utils::handle_text($node->revision->node_content));
		$content .= '<br />';
		$content .= '<span style="font-size: 11px;">' . sprintf(__('Posted on %s'), date('d-m-Y G:i:s', $date)) . '</span>';
		
		$template->set_alt_filename('node-' . $node->node_id, array('module-' . $args['location'] . '.php', 'module.php'));
		$template->assign_priv_vars('node-' . $node->node_id, array(
			'title' 	=> '<a href="' . $page->get_link($node) . '" class="newstitle">' . $node->title . '</a>',
			'content' 	=> $content,
		));
		echo $template->assign_display('node-' . $node->node_id);
	}
	
	function newsfolder_select($node) {
		if ($node->type == 'site' || $node->type == 'newsfolder') {
			return true;
		}
		
		return false;
	}
	
	function options_news() {
		return array();
	}
	
	function options_newsfolder() {
		return array();
	}
	
	function extinfo() {
		return array(
			'version' => '0.0.1',
			'name' => __('News extension'),
			'description' => __('The news extension provides a news system ;)')
		);
	}
	
	function get_url_callback($node) {
		if ($node->type == 'news') {
			$page = page::getnew(false);
			$parents = $page->get_parents($node);
			$folder = $parents[count($parents) - 2];
			
			$path = $node->title_clean;
			$path = $folder->title_clean . '/' . $path; 
			if ($node->extension) {
				$path .= '.' . $node->extension; 
			}
			
			//$urls = array_merge($urls, array(
			return array(
				$path => array(
					'callback' => array('extension_news', 'show_newspage'),
					'cbtype' => 'create_new',
					'parameters' => array(
						'node_id' => $node->node_id
					)
				)
			);
		}
	}
	
	function show_newspage($args) {
		$page = page::getnew();
		$node = new CMS_Node();
		$node->node_id = $args['node_id'];
		$node->read();
		$page->init_page($node);
		ob_start();
		$this->show_news($node, array('location' => 'middle'));
		$c = ob_get_contents();
		ob_end_clean();
		$template = template::getnew();
		$template->assign_vars(array(
			'title' => $node->title,
			'sitename' => $page->sitenode->title,
			'sitedescription' => $page->sitenode->description,
			'middle' => $c,
		));
	}

	function url_parsers() {
		return array(
			'@/news/([a-z0-9\-]+)$@' => array(
				1 => 'news_title'
			)
		);
	}

	function formapi_display($id, $form, $api) {
		if ($id == 'node_add_form') {
			global $node;
			
			if ((!empty($node->type) && $node->type == 'news') || ($api->hiddenfields['node_add_form_type']['value'] == 'news')) {
				unset($api->place[$api->textfields['node_add_form_extension']['place']]);
				unset($api->place[$api->textareas['node_add_form_description']['place']]);
				unset($api->textfields['node_add_form_extension']);
				unset($api->textareas['node_add_form_description']);
				$api->_add_formfield(array(
					'name' => 'extension',
					'type' => 'hidden',
					'value' => 'html'
				));
				$api->_add_formfield(array(
					'name' => 'description',
					'type' => 'hidden',
					'value' => '!'
				));
			}
		}
	}
	
	function admin_get_actions($id) {
		if ($_GET['id'] == 'site_content') {
			utils::get_types();
			$node = new CMS_Node();
			$node->node_id = intval($_GET['node']);
			$node->read();
			
			if ($node->type == 'newsfolder') {
				return array(
					'content' => array(
						'data' => array(
							'add_news' => array(
								'title' => __('Add new news item'),
								'callback' => array('core', 'admin_node_add'),
								'params' => array(
									'node' => $_GET['node'],
									'do' => 'new',
									'type' => 'news::news'
								),
								'image' => 'extensions/news/big-news.png',
								'description' => __('Add a new news item in this news folder.')
							)
						)
					)
				);
			}
		}
	}
}
?>