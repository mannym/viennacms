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
		/*$fnc_txt = 'if(!preg_match("#;[[:space:]]$#is", $matches[2])) { $matches[2] = $matches[2] . ";"; } ob_start(); eval($matches[2]); $c = ob_get_contents(); ob_end_clean(); return $c;';
		//$text = stripslashes($args['content']);
		//$text = utils::handle_text(preg_replace_callback('|<\?(php)?(.*?)\?>|is', create_function('$matches', $fnc_txt), $text));
		echo $text;
		*/
		ob_start();
		eval('?>' . $args['content']);
		$content = ob_get_contents();
		ob_end_clean();
		$content = utils::handle_text($content);
		echo $content;
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

		utils::add_css('file', 'extensions/core/form.css');
		$user = user::getnew();
		$user->initialize(true);
		
		if ($user->user_logged_in) {
			$callback = utils::url('inline-edit/');
			$admin_modules = utils::url('adm/admin_node_modules.php', array('nonsys_url' => true));
			
			utils::add_js('file', 'includes/js/tinymce/tiny_mce.js');
			utils::add_js('file', 'adm/js/jquery.js');
			utils::add_js('file', 'adm/js/jquery.form.js');
			utils::add_js('file', 'extensions/core/inline-edit.js');
			utils::add_js('inline', <<<JS
var inlineedit_cb = '$callback';
var inlineedit_ad = '$admin_modules';
			tinyMCE.init({
				mode : "textareas",
				theme : "advanced",
				editor_selector : "wysiwyg",
				plugins : "nodelink,viennafiles",
				theme_advanced_buttons3_add_before : "nodelink,viennafiles"
			});			
JS
);
		}
	}
	
	function admin_init() {
		$css = <<<CSS
.nodes a.link { background: url(../extensions/core/link.png) 0 0 no-repeat; }
CSS;
		utils::add_css('inline', $css);
		utils::add_css('file', 'extensions/core/form.css');
	}
	
	function inline_edit_callback($args) {
		switch ($args['matches'][1]) {
			case 'form':
				list($function, $key, $location, $node_id) = explode('-', $_POST['id']);
				$uargs = '';
				$uargs .= '&node=' . $node_id;
				$uargs .= '&location=' . $location;
				$uargs .= '&key=' . $key;
				header('Location: ' . utils::url('adm/admin_node_modules.php?mode=args&ajax' . $uargs, array('nonsys_url' => true)));
			break;
			case 'getmodule':
				$page = page::getnew(false);
				list($function, $key, $location, $node_id) = explode('-', $_POST['id']);
				$page->node = new CMS_Node();
				$page->node->node_id = $node_id;
				$page->node->read();
				$page->init_page($page->node);
				
				//echo $page->get_module($page->node->revision->modules[$location][$key], $location, $key);
				echo $page->get_loc_real($location);	
			break;
		}
		
		return 500;
	}

	function url_callbacks() {
		$urls = array();
		$page = page::getnew(false);
		$urls = $this->recursive_urls($page->sitenode, $urls);

		$urls['inline-edit/%text'] = array(
			'callback' => array('extension_core', 'inline_edit_callback'),
			'cbtype' => 'create_new'
		);

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
				if(isset($node->options['display_in_menu']) && !$node->options['display_in_menu'] && $node->type == 'page')
				{
					return false;
				}
				return true;
			break;
			
		}
		return true;
	}

	function get_admin_tree() {
		$node = new CMS_Node();
		$node->node_id = 0;
		echo $this->_get_admin_tree($node);
	}
	
	function _get_admin_tree($node, $list = '') {
		utils::get_types();
		
		if ($node->node_id != 0) {
			$ext = utils::load_extension(utils::$types[$node->type]['extension']);
			//$show = true;
			$show = utils::display_allowed('in_tree', $node);
			/*if (method_exists($ext, $node->type . '_in_tree')) {
				$function = $node->type . '_in_tree';
				$show = $ext->$function($node);
			}*/
		} else {
			$show = true;
		}
		
		if ($show) {
			if ($node->node_id != 0) {
				$list .= '<li id="node-' . $node->node_id . '"><a href="index.php?action=show_actions&id=' . $_GET['id'] . '&node=' . $node->node_id . '" class="' . $node->type . '">' . $node->title . '</a>' . "\r\n";			
			}
			
			$nodes = $node->get_children();

			$my_id = $node->node_id;
			
			$list .= '<ul>';
			if ($nodes) {
				foreach ($nodes as $node) {
					$list = $this->_get_admin_tree($node, $list);
				}
			}
			if ($my_id != 0) {
				$list .= '<li id="node-add"><a href="' . admin::get_callback(array('core', 'admin_node_add'), array('node' => $my_id, 'do' => 'new')) .
					'" class="addnode">' . __('Add') . '</a></li>';
			}
			$list .= '</ul>';
			
			$list .= '</li>';
		}
		return $list;
	}
	
	function admin_get_mainitems() {
		return array(
			'site_structure' => array(
				'image' => 'adm/style/images/structure.png',
				'title' => __('Site structure'),
				'extension' => 'core'
			)
		);
	}
	
	function admin_get_actions($id) {
		$node = new CMS_Node();
		$node->node_id = intval($_GET['node']);
		$node->read();
		
		if ($id == 'site_structure') {
			return array(
				'options' => array(
					'title' => __('Options'),
					'image' => 'adm/style/images/applications.png',
					'data' => array(
						'node_details' => array(
							'title' => __('Edit node details'),
							'callback' => array('core', 'admin_node_add'),
							'params' => array(
								'node' => $_GET['node'],
								'do' => 'edit'
							),
							'image' => 'adm/images/edit.png',
							'description' => __('Edit the details of this node, like the title and description.')
						)
					)
				)
			);
		}
	}
	
	function admin_node_add($args) {
		global $do, $parent, $node, $easy, $mode, $page;
		
		$mode = isset($args['mode']) ? $args['mode'] : 'form';
		$do = isset($_REQUEST['do']) ? $_REQUEST['do'] : $args['do'];
		$easy = (isset($_POST['easy']));
		if ($do == 'new') {
			$parent = new CMS_Node();
			$parent->node_id = (isset($args['node'])) ? $args['node'] : $_POST['node_id'];
			$parent->read();
			$node = CMS_Node::getnew();
			$node_id = $parent->node_id;
		} else if ($do == 'edit') {
			$node_id = (isset($args['node'])) ? $args['node'] : $_POST['node_id'];
			$node = new CMS_Node();
			$node->node_id = $node_id;
			$node->read();
			$parents = $node->get_parent();
			$parent = $parents[0];
		}
		
		$page = page::getnew(false);
		
		include(ROOT_PATH . 'extensions/core/node_add_form.php');
		
		switch ($mode) {
			case 'next':
			case 'form':
			default:
				$type_options	= utils::run_hook_all('list_types');
				$page_title		= $do == 'edit' ? __('viennaCMS ACP - Edit a node') : __('viennaCMS ACP - Add a new node');
				if (!$easy) {
					if ($do == 'new') {
						$title = sprintf(__('Add a new node under %s'), $parent->title);
					} else {
						$title = sprintf(__('Edit the node %s'), $node->title);
					}
				} else {
					$title = sprintf(__('Content wizard, step %d of %d'), 2, 4);	
				}
				$form = new node_add_form;
				$form->elements = array(
					$title => array(
		
					)
				);
				$form->form_id = 'node_add_form';
				$form->action = admin::get_callback(array('core', 'admin_node_add'), $args);
				if (!$easy && $do != 'edit') {
					$values = array('' => array(
						'title' => '--' . __('Select') . '--',
						'selected' => false
					));
					
					foreach($type_options as $type => $extension) {
						$tempnode = new CMS_Node();
						$tempnode->type = $type;
						$ext = utils::load_extension($extension['extension']);
						$show = utils::display_allowed('this_under_other', $tempnode, $parent);
						unset($tempnode);
						/*if (method_exists($ext, $type . '_allow_as_child')) {
							$function = $type . '_allow_as_child';
							$show = $ext->$function($parent);
						}*/
						
						if (!$show) {
							continue;
						}
						$values[$type] = array(
							'title' => $type,
							'selected' => ($node->type == $type)
						);
					}
					$form->elements[$title]['type'] = array(
						'type' => 'selectbox',
						'name' => 'type',
						'title' => __('Type'),
						'description' => __('Select the type of the node'),
						'value' => $values,
						'required' => true
					);
				} else if ($easy) {
					$type = explode('::', base64_decode($_POST['type']));
					$type = $type[1];
					$form->elements[$title]['type'] = array(
						'name' => 'type',
						'type' => 'hidden',
						'value' => $type
					);
					$form->elements[$title]['easy'] = array(
						'name' => 'easy',
						'type' => 'hidden',
						'value' => 'true',
						'raw' => true
					);
				}
				$form->elements[$title]['title'] = array(
					'type'			=> 'textfield',
					'name'			=> 'title',
					'title'			=> __('Title'),
					'description'	=> __('Enter the title for the node. This title will be automatically cleaned.'),
					'value'			=> $node->title,
					'required'		=> true,
				);
				$form->elements[$title]['title_clean'] = array(
					'type'			=> 'textfield',
					'name'			=> 'title_clean',
					'title'			=> __('Clean Title'),
					'description'	=> __('The clean title for the node. When changing the title, this will be automatically generated.'),
					'value'			=> $node->title_clean,
					'required'		=> true,
				);
				$form->elements[$title]['extension'] = array(
					'type'			=> 'textfield',
					'name'			=> 'extension',
					'title'			=> __('Extension'),
					'description'	=> __('Enter the extension. By example, html. Don\'t put a dot (\'.\') at the begin of the extension!.'),
					'value'			=> $node->extension,
					'max_length'	=> 6,
				);
				$form->elements[$title]['description'] = array(
					'type'			=> 'textarea',
					'name'			=> 'description',
					'title'			=> __('Description'),
					'description'	=> __('Enter the description for the node'),
					'value'			=> $node->description,
					'required'		=> true
				);
				$form->elements[$title]['do'] = array(
					'name' => 'do',
					'type' => 'hidden',
					'value' => $do,
					'raw' => true
				);
				$form->elements[$title]['node_id'] = array(
					'name' => 'node_id',
					'type' => 'hidden',
					'value' => $node_id,
					'raw' => true
				);
				
				$api = new formapi;
				echo $api->get_form($form);
				?>
				<script type="text/javascript">
					$('#node_add_form_title').blur(function () {
						$.get('<?php echo utils::base() ?>ajax.php?mode=cleantitle&title=' + escape($('#node_add_form_title').attr('value')), '', function(data, textStatus) {
							$('#node_add_form_title_clean').attr('value', data);
						});
					});
				</script>
				
				<?php
			break;
		}
	}
	
	function admin_left_site_structure() {
		echo '<ul class="nodes" id="tree" style="display: block;">';
		echo $this->get_admin_tree();
		echo '</ul>';
	}
}
?>
