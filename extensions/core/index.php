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

class extension_core {
	function list_modules() {
		return array(
			'html_content' => 'core',
			'sitemap' => 'core'
		);
	}
	
	function list_types() {
		return array(
			'site' => array(
				'extension' => 'core',
				'type' => NODE_MODULES
			),
			'page' => array(
				'extension' => 'core',
				'type' => NODE_MODULES
			),
			'link' => array(
				'extension' => 'core',
				'type' => NODE_NO_REVISION
			)
		);
	}
	
	function args_html_content() {
		return array('content' => array( // 'content' is the argument name
			'title' => __('Content'), // title is what it will show
			'type' => 'wysiwyg', // textarea or text, currently
			'newrow' => true // true to make the control 100% width in ACP
		));
	}
	
	function module_html_content($args) {
		echo nl2br(utils::handle_text(stripslashes($args['content'])));
	}

	function args_sitemap() {
		return array('dummy' => array(
			'title' => __('Options for sitemap'),
		));
	}
	
	function module_sitemap($args) {
		$page = page::getnew();
		utils::add_css('inline', "
.treeview, .treeview ul { 
	padding: 0;
	margin: 0;
	list-style: none;
	color: #000;
}

.treeview div.hitarea {
	height: 15px;
	width: 15px;
	margin-left: -15px;
	float: left;
	cursor: pointer;
}
/* fix for IE6 */
* html div.hitarea {
	filter: alpha(opacity=0);
	display: inline;
	float:none;
}

.treeview li { 
	margin: 0;
	padding: 3px 0pt 3px 16px;
}

.treeview a.selected {
	background-color: #333;
}

#treecontrol { margin: 1em 0; }

.treeview .hover { color: red; cursor: pointer; }

.treeview li { background: url(adm/images/tv-item.gif) 0 0 no-repeat; }
.treeview li a { padding-left: 5px; font-size: 12px; }
.treeview li span { font-size: 10px; padding-left: 10px; }
.treeview .collapsable { background-image: url(adm/images/tv-collapsable.gif); }
.treeview .expandable { background-image: url(adm/images/tv-expandable.gif); }
.treeview .last { background-image: url(adm/images/tv-item-last.gif); }
.treeview .lastCollapsable { background-image: url(adm/images/tv-collapsable-last.gif); }
.treeview .lastExpandable { background-image: url(adm/images/tv-expandable-last.gif); }
");
		utils::get_types();
		echo '<ul class="treeview">';
		echo $this->_get_map_tree($page->sitenode);
		echo '</ul>';
	}
	

	function _get_map_tree($node, $list = '', $extra = array()) {
		$ext = utils::load_extension(utils::$types[$node->type]['extension']);
		$show = true;
		if (method_exists($ext, $node->type . '_show_to_visitor')) {
			$function = $node->type . '_show_to_visitor';
			$show = $ext->$function($node);
		}

		if ($show) {	
			$page = page::getnew();
			if ($node->node_id != 0) {
				$class = '';
				if ($extra['i'] == $extra['mi']) {
					$class = ' class="last"';
				}
				$list .= '<li' . $class . '><a href="' . $page->get_link($node) . '">' . $node->title . '</a>' . '<span>' . $node->description . "</span>\r\n";			
			}
			
			$nodes = $node->get_children();
			
			if ($nodes) {
				$list .= '<ul>';
				$i = 1;
				$maxi = 0;
				foreach ($nodes as $node) {
					$ext = utils::load_extension(utils::$types[$node->type]['extension']);
					$show = true;
					if (method_exists($ext, $node->type . '_show_to_visitor')) {
						$function = $node->type . '_show_to_visitor';
						$show = $ext->$function($node);
					}
					
					if ($show) {
						$maxi++;
					}
				}
				//$maxi = count($nodes);
				foreach ($nodes as $node) {
					$list = $this->_get_map_tree($node, $list, array('i' => $i, 'mi' => $maxi));
					$ext = utils::load_extension(utils::$types[$node->type]['extension']);
					$show = true;
					if (method_exists($ext, $node->type . '_show_to_visitor')) {
						$function = $node->type . '_show_to_visitor';
						$show = $ext->$function($node);
					}
					
					if ($show) {
						$i++;
					}
				}
				$list .= '</ul>';
			}
			
			$list .= '</li>';
		}
		
		return $list;
	}
	
	function options_site() {
		return array(
			'hostname' => array(
				'title' => __('Hostname'),
				'description' => __('The hostname where this site node should be displayed.')
			),
			'rewrite' => array(
				'title' => __('Rewrite'),
				'description' => __('Use mod_rewrite for this site? Leave empty to disable, else type "on".')
			)
		);
	}

	function options_link() {
		return array(
			'destination' => array(
				'title' => __('Destination'),
				'description' => __('The URL where the link should go to.')
			)
		);
	}
	
	function options_page() {
		return array();
	}

	
	function extinfo() {
		return array(
			'version' => '0.0.1',
			'name' => __('Core extension'),
			'description' => __('The core extension provides some basic modules.')
		);
	}
	
	function before_display() {
		$page = page::getnew(false);
		if ($page->node->type == 'link') {
			if (empty($page->node->options['destination'])) {
				header('Location: ' . $page->get_link($page->sitenode));
				exit;
			}
			
			header('Location: ' . $page->node->options['destination']);
			exit;
		}
	}
	
	function admin_init() {
		$css = <<<CSS
.nodes a.link { background: url(../extensions/core/link.png) 0 0 no-repeat; }
CSS;
		utils::add_css('inline', $css);
	}
}
?>