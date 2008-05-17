<?php
/**
* Core extension for viennaCMS
* 
* @package viennaCMS
* @author viennacms.nl
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*/

if (!defined('IN_VIENNACMS')) {
	exit;
}

class extension_core 
{
	function list_modules() {
		return array(
			'html_content' => 'core',
			'sitemap' => 'core',
			'rawcontent' => 'core',
		);
	}
	
	function list_types() {
		return array(
			'site' => array(
				'extension' => 'core',
				'type' => NODE_MODULES,
				'allow_easy' => true
			),
			'page' => array(
				'extension' => 'core',
				'type' => NODE_MODULES,
				'allow_easy' => true
			),
			'link' => array(
				'extension' => 'core',
				'type' => NODE_NO_REVISION,
				'allow_easy' => true
			),
		);
	}
	
	function args_html_content() {
		return array('content' => array( // 'content' is the argument name
			'title' => __('Content'), // title is what it will show
			'type' => 'wysiwyg', // textarea, text or wysiwyg
			'newrow' => true // true to make the control 100% width in ACP
		));
	}
	
	function args_rawcontent() {
		return array('content' => array( // 'content' is the argument name
			'title' => __('Content'), // title is what it will show
			'type' => 'textarea', // textarea, text or wysiwyg
			'newrow' => true // true to make the control 100% width in ACP
		));
	}
	
	function dynamic_rawcontent() {
		return true;
	}
	
	function module_html_content($args) {
		echo utils::handle_text(stripslashes($args['content']));
	}
	
	function module_rawcontent($args) {
		$fnc_txt = 'if(!preg_match("#;[[:space:]]$#is", $matches[2])) { $matches[2] = $matches[2] . ";"; } ob_start(); eval($matches[2]); $c = ob_get_contents(); ob_end_clean(); return $c;';
		$text = stripslashes($args['content']);
		$text = utils::handle_text(preg_replace_callback('|<\?(php)?(.*?)\?>|is', create_function('$matches', $fnc_txt), $text));
		echo $text;
	}

	function args_sitemap() {
		return array('dummy' => array(
			'title' => __('Options for sitemap'),
		));
	}
	
	function module_sitemap($args) {
		global $cache;
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
		$cache_id = '_module_sitemap_' . $page->sitenode->node_id;
		if (($content = $cache->get($cache_id)) !== false) {
			echo $content;
		} else {
			ob_start();
			utils::get_types();
			echo '<ul class="treeview">';
			echo $this->_get_map_tree($page->sitenode);
			echo '</ul>';
			$content = ob_get_contents();
			ob_end_clean();
			$cache->put($cache_id, $content, 7200);
			echo $content;
		}		
	}
	

	function _get_map_tree($node, $list = '', $extra = array()) {
		$show = utils::display_allowed('show_to_visitor', $node);
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
					$show = utils::display_allowed('show_to_visitor', $node);
					
					if ($show) {
						$maxi++;
					}
				}
				foreach ($nodes as $node) {
					$list = $this->_get_map_tree($node, $list, array('i' => $i, 'mi' => $maxi));
					$show = utils::display_allowed('show_to_visitor', $node);
					
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
	
	function options_site($options) {
		$db = database::getnew();
		$sql = "SELECT * FROM " . NODES_TABLE . " WHERE type = 'site'";
		$result = $db->sql_query($sql);
		$affectedrows = $db->sql_affectedrows($result);
		$array = array();
		if(count($affectedrows) > 1)
		{
			$array = array(	'hostname' => array(
				'type'			=> 'textfield',
				'name'			=> 'hostname',
				'title'			=> __('Hostname'),
				'description'	=> __('The hostname where this site node should be displayed. Leave empty if not using a multi-site setup.'),
				'value'			=> $options['hostname']
			));
		}
		return array_merge($array, array(
			'rewrite' => array(
				'type'			=> 'selectbox',
				'name'			=> 'rewrite',
				'title'			=> __('Clean URLs'),
				'description'	=> __('Use clean URLs for this site?'),
				'value'			=> array(
					'on' => array(
						'title' => 'On',
						'selected' => ($options['rewrite'] == 'on')
					),
					'' => array(
						'title' => 'Off',
						'selected' => ($options['rewrite'] != 'on')
					)
				)
			),
			'language' => array(
				'type'			=> 'language',
				'name'			=> 'language',
				'title'			=> __('Language'),
				'description'	=> __('Specify the language to use for this node'),
				'value'			=> $options['language']
			)
		));
	}
	

	function options_link($options) {
		return array(
			'destination' => array(
				'name'			=> 'destination',
				'type'			=> 'textfield',
				'title'			=> __('Destination'),
				'description'	=> __('The URL where the link should go to.'),
				'value'			=> $options['destination']
			)
		);
	}
	
	function options_page($options) {
		$show = isset($options['display_in_menu']) ? (bool) $options['display_in_menu'] : true;
		//$no_selected  = $yes_selected ? false : true;
		return array(
			'display_in_menu' => array(
				'name'			=> 'display_in_menu',
				'type'			=> 'radio',
				'title'			=> __('Display in menu'),
				'description'	=> __('Select \'No\' to hide this item from the menu.'),
				'sameline'		=> true,
				'values'		=> array(
					(int) true		=> array(
						'title'			=> __('Yes'),
						'selected'		=> 	$show,
					),
					(int) false		=> array(
						'title'			=> __('No'),
						'selected'		=> (!$show),
					),
				),
			),
		);
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

	function url_callbacks() {
		$urls = array();
		$page = page::getnew(false);
		$urls = $this->recursive_urls($page->sitenode, $urls);
		return $urls;
	}
	
	function recursive_urls($node, $urls) {
		$urls = array_merge($urls, utils::run_hook_all('get_url_callback', $node));
		
		$children = $node->get_children();
		foreach ($children as $child) {
			$urls = $this->recursive_urls($child, $urls);
		}
		
		return $urls;
	}
	
	function get_url_callback($node) {
		if ($node->type == 'page' || $node->type == 'site' || $node->type == 'link') {
			$path = $node->title_clean;
			if ($node->parentdir) {
				$path = $node->parentdir . '/' . $path; 
			}
			if ($node->extension) {
				$path .= '.' . $node->extension; 
			}

			$rpath = $node->title_clean;
			if ($node->parentdir) {
				$rpath = $node->parentdir . '/' . $rpath; 
			}
			$rpath .= '/revision/%number';
			if ($node->extension) {
				$rpath .= '.' . $node->extension; 
			}
			
			//$urls = array_merge($urls, array(
			return array(
				$path => array(
					'callback' => array('page', 'show_node'),
					'cbtype' => 'create_new_getnew',
					'parameters' => array(
						'node_id' => $node->node_id
					)
				),
				$rpath => array(
					'callback' => array('page', 'show_node'),
					'cbtype' => 'create_new_getnew',
					'parameters' => array(
						'node_id' => $node->node_id,
						'revision' => true
					)
				)
			);
		}
	}
	
	function display_node($type, $node, $other) {
		switch($type) {
			case 'show_to_visitor':
				if(isset($node->options['display_in_menu']) && settype($node->options['display_in_menu'], 'bool') && $node->type == 'page')
				{
					return false;
				}
				return true;
			break;
			
		}
		return true;
	}
}
?>
