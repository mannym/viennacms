<?php
/**
* Core extension for viennaCMS
* 
* @package viennaCMS
* @author viennainfo.nl
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*/

if (!defined('IN_viennaCMS')) {
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
		return array('site', 'page');
	}
	
	function args_html_content() {
		return array('content' => array( // 'content' is the argument name
			'title' => __('Content'), // title is what it will show
			'type' => 'wysiwyg', // textarea or text, currently
			'newrow' => true // true to make the control 100% width in ACP
		));
	}
	
	function module_html_content($args) {
		echo nl2br(stripslashes($args['content']));
	}

	function args_sitemap() {
		return array('dummy' => array(
			'title' => __('Options for sitemap'),
		));
	}
	
	function module_sitemap($args) {
		$page = page::getnew();
		?>
		<style type="text/css">
		

.treeview, .treeview ul { 
	padding: 0;
	margin: 0;
	list-style: none;
	color: #EEE;
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
		</style>
		<div style="background-color: #333;">
		<?php
		echo '<ul class="treeview">';
		echo $this->_get_map_tree($page->sitenode);
		echo '</ul>';
		?></div><?php
	}
	

	function _get_map_tree($node, $list = '', $extra = array()) {
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
			$maxi = count($nodes);
			foreach ($nodes as $node) {
				$list = $this->_get_map_tree($node, $list, array('i' => $i, 'mi' => $maxi));
				$i++;
			}
			$list .= '</ul>';
		}
		
		$list .= '</li>';
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
}
?>