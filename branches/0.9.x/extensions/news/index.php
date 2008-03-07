<?php
/**
* Core extension for viennaCMS
* 
* @package viennaCMS
* @author viennainfo.nl
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
			'latestnews' => 'news'
		);
	}
	
	function list_types() {
		return array(
			'newsfolder' => array(
				'extension' => 'news',
				'type' => NODE_NO_REVISION
			),
			'news' => array(
				'extension' => 'news',
				'type' => NODE_CONTENT,
				'field' => 'wysiwyg'
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
	
	function args_latestnews() {
		return array('folder' => array( // 'content' is the argument name
			'title' => __('News folder'), // title is what it will show
			'type' => 'node', // textarea or text, currently
			'newrow' => false, // true to make the control 100% width in ACP
			'callback' => array($this, 'newsfolder_select')
		));
	}
	
	function module_latestnews($args) {
		if (isset($_GET['news_title'])) {
			$node = new CMS_Node();
			$node->title_clean = $_GET['news_title'];
			$node->read(NODE_TITLEC);
			$this->show_news($node, $args);
			return 500;
		}
		
		$template = template::getnew();
	
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
			if ($i > 5) {
				break;
			}
			
			$this->show_news($new, $args);

			$i++;
		}
		
		return 500;
	}
	
	function show_news($node, $args) {
		$date = $node->created;
	
		$template = template::getnew();
		$page = page::getnew(false);
	
		$content = nl2br(utils::handle_text($node->revision->node_content));
		$content .= '<br />';
		$content .= '<span style="font-size: 11px;">' . sprintf(__('Posted on %s'), date('d-m-Y G:i:s', $date)) . '</span>';
		
		$template->set_alt_filename('node-' . $node->node_id, array('module-' . $args['location'] . '.php', 'module.php'));
		$template->assign_vars(array(
			'title' 	=> '<a href="' . $page->get_link($page->node, '/news/' . $node->title_clean) . '">' . $node->title . '</a>',
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
	
	function url_parsers() {
		return array(
			'@/news/([a-z0-9\-]+)$@' => array(
				1 => 'news_title'
			)
		);
	}
}
?>