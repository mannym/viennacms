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
				'allow_easy' => false,
				'description' => __('A site is, as the name says, a site. Adding this node will enable multisite features.'),
				'title' => __('Site')
			),
			'page' => array(
				'extension' => 'core',
				'type' => NODE_MODULES,
				'allow_easy' => true,
				'description' => __('A page is the most basic part of a viennaCMS web site. It appears in the menu, and you can add other content to it.'),
				'title' => __('Page')
			),
			'link' => array(
				'extension' => 'core',
				'type' => NODE_NO_REVISION,
				'allow_easy' => true,
				'description' => __('A link creates a menu option which links through to a selected location.'),
				'title' => __('Link')
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
			
			if ($_GET['id'] == 'site_structure') {
				$list .= '<ul>';
			}
			
			if ($nodes) {
				if ($_GET['id'] != 'site_structure') {
					$list .= '<ul>';
				}
				foreach ($nodes as $node) {
					$list = $this->_get_admin_tree($node, $list);
				}
				if ($_GET['id'] != 'site_structure') {
					$list .= '</ul>';
				}
			}
			if ($_GET['id'] == 'site_structure' && $my_id != 0) {
				$list .= '<li id="node-add"><a href="' . admin::get_callback(array('core', 'admin_node_add'), array('node' => $my_id, 'do' => 'new', 'mode' => 'initial')) .
					'" class="addnode">' . __('Add') . '</a></li>';
			}
			
			if ($_GET['id'] == 'site_structure') {
				$list .= '</ul>';
			}
			
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
			),
			'site_content' => array(
				'image' => 'adm/style/images/content.png',
				'title' => __('Content'),
				'extension' => 'core'
			),
			'site_config' => array(
				'image'	=> 'adm/style/images/config.png',
				'title' => __('Config'),
				'extension' => 'core',
			),
			'logout'	=> array(
				'image' => 'adm/style/images/logout.png',
				'title'	=> __('Logout'),
				'extension' => 'core',
				'no_load'	=> true,
				'class' => 'right'
			),
			'viewsite'	=> array(
				'image' => 'adm/style/images/viewsite.png',
				'title'	=> __('View site'),
				'extension'	=> 'core',
				'class' => 'right'
			),
		);
	}
	
	function admin_get_actions($id) {
		utils::get_types();
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
						),
						'node_options' => array(
							'title' => __('Edit options'),
							'callback' => array('core', 'admin_node_options'),
							'params' => array(
								'node' => $_GET['node']
							),
							'image' => 'adm/style/images/node-options.png',
							'description' => __('Edit the options of this node, like for example the template, or a link target.')
						),
					)
				),
				'structure' => array(
					'title' => __('Structure'),
					'image' => 'adm/style/images/applications.png',
					'data' => array(
						'node_delete' => array(
							'title' => __('Delete node'),
							'callback' => array('core', 'admin_node_delete'),
							'params' => array(
								'node' => $_GET['node']
							),
							'image' => 'adm/images/edit_remove.png',
							'description' => __('Delete this node permanently.')
						)
					)
				),
			);
		}
		
		if ($id == 'site_content') {
			$return = array(
				'content' => array(
					'title' => __('Content'),
					'image' => 'adm/style/images/applications.png',
					'data' => array()
				)
			);
			
			if (utils::$types[$node->type]['type'] == NODE_CONTENT) {
				$return['content']['data']['node_content'] = array(
					'title' => __('Edit content'),
					'callback' => array('core', 'admin_node_content'),
					'params' => array(
						'node' => $_GET['node'],
					),
					'image' => 'adm/images/modules.png',
					'description' => __('Add or edit the text content in this node.')
				);
			}
			
			if (utils::$types[$node->type]['type'] == NODE_MODULES) {
				$return['content']['data']['node_modules'] = array(
					'title' => __('Edit modules'),
					'callback' => array('core', 'admin_node_modules'),
					'params' => array(
						'node' => $_GET['node'],
						'mode' => 'choose'
					),
					'image' => 'adm/images/modules.png',
					'description' => __('Add or edit the modules in this node.')
				);
			}
			
			if ($node->type == 'site') {
				$return['actions']['data']['node_blocks'] = array(
					'title' => __('Edit blocks'),
					'callback' => array('core', 'admin_node_modules'),
					'params' => array(
						'node' => $_GET['node'],
						'mode' => 'choose',
						'blocks' => true
					),
					'image' => 'adm/images/modules.png',
					'description' => __('Add or edit the blocks for this site.')
				);
			}
			
			if (utils::$types[$node->type]['type'] != NODE_NO_REVISION) {			
				$return['actions']['data']['node_revisions'] = array(
					'title' => __('View older versions'),
					'callback' => array('core', 'admin_node_revisions'),
					'params' => array(
						'node' => $_GET['node']
					),
					'image' => 'adm/images/revisions.png',
					'description' => __('View older versions of this node, which are saved automatically while editing.')
				);
			}
			
			if (empty($return['actions']['title'])) {
				$return['actions']['title'] = __('Actions');
				$return['actions']['image'] = 'adm/style/images/applications.png';
			}
			
			return $return;
		}
	}
	
	function admin_node_content($args) {
		$mode = isset($args['mode']) ? $args['mode'] : 'form';
		$node = new CMS_Node();
		$node->node_id = $args['node'];
		$node->read();
		
		utils::get_types();
		
		switch($mode) {
			case 'save':
				$node->revision->node_content = $_POST['node_content'];
				$node->write();
				//header('Location: ' . utils::base() . 'admin_node.php?node=' . $node->node_id);
				?> 
				<script type="text/javascript">
					load_in_system('index.php?action=show_actions&id=site_content&node=<?php echo $node->node_id ?>', 'site_content');
				</script>
				<?php
				exit;	
			break;
				
			default:
			case 'form':
				$thisargs = $args;
				$thisargs['mode'] = 'save';
				?>
				<form action="<?php echo admin::get_callback(array('core', 'admin_node_content'), $thisargs) ?>" method="post">
					<?php
					$key = 'node_content';
					$val = $node->revision->node_content;
					
					switch (utils::$types[$node->type]['field']) {
						case 'wysiwyg':
						?>
						<textarea class="wysiwyg" id="wysiwyg_form" name="<?php echo $key ?>" style="width: 500px; height: 250px;"><?php echo stripslashes(preg_replace("#\<br \/\>#", '', $val)); ?></textarea>
						<?php
						break;
						case 'textarea':
						default:
							?>
							<textarea name="<?php echo $key ?>" rows="5" cols="40"><?php echo stripslashes(preg_replace('#\<br \/\>#', '', $val)); ?></textarea>
							<?php				
						break;
					}
					?><br />
					<input type="hidden" name="node" value="<?php echo $node->node_id ?>" />
					<input type="submit" value="<?php echo __('Save') ?>" />
				</form>
				
				<?php
			break;
		}
	}
	
	function admin_node_modules($args) {
		global $node, $myargs;
		$myargs = $args;
		$mode = (isset($args['mode'])) ? $args['mode'] : $_POST['mode'];
		$node = new CMS_Node();
		$node->node_id = (isset($args['node'])) ? $args['node'] : $_POST['node'];
		$node->read();
		$blocks = (isset($args['blocks']) || isset($_POST['blocks']));
		if ($blocks) {
			$node->options['blocks'] = unserialize($node->options['blocks']);
		}
		//$page_title = __("viennaCMS ACP - Edit the node modules");
		
		switch($mode) {
			case 'move':
				$data = explode('-', $_POST['id']);
				$node = new CMS_Node();
				$node->node_id = $data[0];
				$node->read();
				if ($blocks) {
					$node->options['blocks'] = unserialize($node->options['blocks']);
					$var = &$node->options['blocks'];
					$thing = &$node->options['blocks'][$data[2]][$data[1]];
				} else {
					$var = &$node->revision->modules;
					$thing = &$node->revision->modules[$data[2]][$data[1]];
				}
				
				$var[$data[2]] = utils::array_move_element($var[$data[2]], $thing, $_POST['type']);
				
				if ($blocks) {
					$this->show_blockform($data[2], false);
					$node->options['blocks'] = serialize($node->options['blocks']);
				} else {
					$this->show_modform($data[2], false);
				}
				
				$node->write();
			break;
			case 'ajax-save':
			case 'save':
				if (!$blocks) {
					if ($_POST['key'] != 'empty') {
						$thing = &$node->revision->modules[$_POST['location']][$_POST['key']];
					} else {
						$thing = &$node->revision->modules[$_POST['location']];
						$thing = &$thing[count($thing)];
					}
				} else {
					if ($_POST['key'] != 'empty') {
						$thing = &$node->options['blocks'][$_POST['location']][$_POST['key']];
					} else {
						$thing = &$node->options['blocks'][$_POST['location']];
						$thing = &$thing[count($thing)];
					}			
				}
				
				if (is_null($thing)) {
					$thing = array();
				}
				
				if (isset($_POST['content_title']) || isset($_GET['ajax'])) {
					foreach ($_POST as $key => $value) {
						if ($key != 'key' && $key != 'location' && $key != 'node' && $key != 'submit' && $key != 'blocks') {
							$thing[$key] = stripslashes($value);
						}
					}
				} else if (isset($args['do']) && $args['do'] == 'delete') {
					if (!$blocks) {
						unset($node->revision->modules[$_POST['location']][$_POST['key']]);
					} else {
						unset($node->options['blocks'][$_POST['location']][$_POST['key']]);
					}
				}
				
				if ($blocks) {
					$node->options['blocks'] = serialize($node->options['blocks']);
				}
				
				$node->write();
				?> 
				<script type="text/javascript">
					load_in_system('index.php?action=show_actions&id=site_content&node=<?php echo $node->node_id ?>', 'site_content');
				</script>
				<?php
				exit;
			break;
			case 'args':
				if (isset($args['location']) && isset($args['key'])) {
					if (!$blocks) {
						$margs = $node->revision->modules[$args['location']][$args['key']];
					} else {
						$margs = $node->options['blocks'][$args['location']][$args['key']];
					}
					$exists = true;
				} else {
					$temp = explode('::', $_POST['extmod']);
					$margs = array(
						'extension' => $temp[0],
						'module' => $temp[1]
					);
					$exists = false;
				}
				
				$sargs = $args;
				$sargs['mode'] = 'save';
				
				?>
				<form action="<?php echo admin::get_callback(array('core', 'admin_node_modules'), $sargs); ?>" id="module_edit_form" method="post">
					<input type="hidden" name="extension" value="<?php echo $margs['extension'] ?>" />
					<input type="hidden" name="module" value="<?php echo $margs['module'] ?>" />
					<input type="hidden" name="location" value="<?php echo $args['location'] ?>" />
					<input type="hidden" name="key" value="<?php echo (isset($args['key'])) ? $args['key'] : 'empty' ?>" />
					<input type="hidden" name="node" value="<?php echo $node->node_id ?>" />
					<table width="100%">
				<?php
				
				$aargs = utils::run_hook_all('args_' . $margs['module']);
				
				foreach ($aargs as $key => $value) {
					$colspan = ($value['newrow']) ? ' colspan="2"' : '';
					?>
					<tr>
						<td<?php echo $colspan ?> style="vertical-align: top;"><strong><?php echo $value['title'] ?>:</strong></td>
						<?php
						if ($value['newrow']) {
							echo '</tr><tr>';
						}
						?>
						<td<?php echo $colspan ?>>
						<?php
						switch ($value['type']) {
							case 'text':
								?>
								<input type="text" name="<?php echo $key ?>" value="<?php echo $margs[$key] ?>" /></td>
								<?php
							break;
							case 'textarea':
								?>
								<textarea name="<?php echo $key ?>" rows="5" cols="40"><?php echo stripslashes(preg_replace('#\<br \/\>#', '', $margs[$key])); ?></textarea>
								<?php
							break;
							case 'wysiwyg':
								?>
								<textarea class="wysiwyg" id="wysiwyg_form" name="<?php echo $key ?>" style="width: 500px; height: 250px;"><?php echo stripslashes($margs[$key]); ?></textarea>
								<?php
							break;
							case 'node':
								if (!isset($value['cbtype'])) {
									$cbtype = 0;
								} else {
									$cbtype = $value['cbtype'];
								}
								echo utils::node_select($key, $value['callback'], $cbtype);
							break;
						}
						?>
						</td>
					</tr>
					<?php
				}
				?>
					<tr>
						<td>
							<strong><?php echo __("Title"); ?></strong><br />
							<?php echo __("Enter the title to display"); ?>
						</td>
						
						<td>
							<input type="text" name="content_title" value="<?php echo $margs['content_title']; ?>" />
						</td>
					</tr>
					
					<tr>
						<td colspan="2">
							<input type="submit" name="submit" value="<?php echo __('Save') ?>" />
						</td>
					</tr>
					</table>
					<?php
					if ($blocks) {
						?>
						<input type="hidden" name="blocks" value="true" />
						<?php
					}
					?>
				</form>
				<?php
				if ($exists) {
					$dargs = $args;
					$dargs['mode'] = 'save';
					$dargs['do'] = 'delete';
					?>
					<form method="post" action="<?php echo admin::get_callback(array('core', 'admin_node_modules'), $dargs); ?>">
						<input type="hidden" name="extension" value="<?php echo $margs['extension'] ?>" />
						<input type="hidden" name="module" value="<?php echo $margs['module'] ?>" />
						<input type="hidden" name="location" value="<?php echo $args['location'] ?>" />
						<input type="hidden" name="key" value="<?php echo (isset($args['key'])) ? $args['key'] : 'empty' ?>" />
						<input type="hidden" name="node" value="<?php echo $node->node_id ?>" />
						<input type="submit" name="delete" value="<?php echo __('Delete') ?>" />
						<?php
						if ($blocks) {
							?>
							<input type="hidden" name="blocks" value="true" />
							<?php
						}
						?>
					</form>
					<?php
				}
			break;
			case 'module':
				/*if (isset($_POST['submit-left'])) {
					$location = 'left';
				} else if (isset($_POST['submit-middle'])) {
					$location = 'middle';
				} else if (isset($_POST['submit-right'])) {
					$location = 'right';
				}*/
				/*foreach ($_POST as $key => $value) {
					if (substr($key, 0, 7) == 'submit-') {
						$location = substr($key, 7);
					}
				}*/
				$aargs = $args;
				$aargs['mode'] = 'args';
				?>
				<form method="post" action="<?php echo admin::get_callback(array('core', 'admin_node_modules'), $aargs) ?>">
					<input type="hidden" name="node" value="<?php echo $node->node_id ?>" />
					<h1><?php echo __('Select module type') ?></h1>
					<?php
					$modules = utils::run_hook_all('list_modules');
					
					foreach ($modules as $module => $extension) {
						?>
						<input type="radio" name="extmod" value="<?php echo $extension . '::' . $module; ?>" /> <?php echo $module ?><br />
						<?php
					}
					if ($blocks) {
						?>
						<input type="hidden" name="blocks" value="true" />
						<?php
					}
					?>
					<input type="submit" value="<?php echo __('Continue') ?>" />
				</form>
				<?php
			break;
			default:
			case 'choose':
				?>
				<script type="text/javascript">
					$(document).ready(function() {
						updateLinks();
					});
					
					function updateLinks(id) {
						if (!id) {
							$('.upmodule').append(' <a href="#" onclick="upMyModule(this.parentNode.id, this.parentNode.parentNode.id); return false;">^</a>');
							$('.upmodule').append(' <a href="#" onclick="downMyModule(this.parentNode.id, this.parentNode.parentNode.id); return false;">v</a>');
						} else {
							$('#' + id + ' .upmodule').append(' <a href="#" onclick="upMyModule(this.parentNode.id, this.parentNode.parentNode.id); return false;">^</a>');
							$('#' + id + ' .upmodule').append(' <a href="#" onclick="downMyModule(this.parentNode.id, this.parentNode.parentNode.id); return false;">v</a>');
						}
					}
		
					<?php
					$argsm = $args;
					$argsm['mode'] = 'move';
					?>
		
					function downMyModule(id, parent) {
						$.ajax({
							cache: false,
							type: "POST",
							url: "<?php echo admin::get_callback(array('core', 'admin_node_modules'), $argsm); ?>",
							data: "<?php echo ($blocks) ? 'blocks=true&' : '' ?>mode=move&type=down&id=" + id,
							success: function(output) {
								$('#' + parent).html(output);
								updateLinks(parent);
							}
						});
					}
					
					function upMyModule(id, parent) {
						$.ajax({
							cache: false,
							type: "POST",
							url: "<?php echo admin::get_callback(array('core', 'admin_node_modules'), $argsm); ?>",
							data: "<?php echo ($blocks) ? 'blocks=true&' : '' ?>mode=move&type=up&id=" + id,
							success: function(output) {
								$('#' + parent).html(output);
								updateLinks(parent);
							}
						});
					}
				</script>
				<?php
				if ($node->type != 'site' || !$blocks) {
				?>
					<div style="width: 33%; float: left;">
						<h2><?php echo __('Left') ?></h2>
						<?php $this->show_modform('left') ?>
					</div>
					<div style="width: 33%; float: left;">
						<h2><?php echo __('Middle') ?></h2>
						<?php $this->show_modform('middle') ?>
					</div>
					<div style="width: 33%; float: left;">
						<h2><?php echo __('Right') ?></h2>
						<?php $this->show_modform('right') ?>
					</div>
				<?php
				} else {
					?>
					<div style="width: 33%; float: left;">
						<h2><?php echo __('Left') ?></h2>
						<?php $this->show_blockform('left') ?>
					</div>
					<div style="width: 33%; float: left;">
						<h2><?php echo __('Before content') ?></h2>
						<?php $this->show_blockform('before_content') ?>
						<h2><?php echo __('After content') ?></h2>
						<?php $this->show_blockform('after_content') ?>
					</div>
					<div style="width: 33%; float: left;">
						<h2><?php echo __('Right') ?></h2>
						<?php $this->show_blockform('right') ?>
					</div>			
					<?php
				}
				?>
				<input type="hidden" name="node" value="<?php echo $node->node_id ?>" />
				<?php
				if ($blocks) {
					?>
					<input type="hidden" name="blocks" value="true" />
					<?php
				}
			break;
		}
	}
	
	function show_blockform($location, $all = true) {
		global $node, $myargs;
		$fargs = $myargs;
		$fargs['mode'] = 'module';
		$fargs['location'] = $location;
		$aargs = $fargs;
		$aargs['mode'] = 'args';
		
		if ($all) {
			if (!$node->options['blocks']) {
				$node->options['blocks'] = array(
					'left' => array(),
					'right' => array(),
					'before_content' => array(),
					'after_content' => array(),
				);
				$node->write_option('blocks', serialize($node->options['blocks']), true);
			}
			echo '<form method="post" action="' . admin::get_callback(array('core', 'admin_node_modules'), $fargs) . '">';
			echo '<ul style="list-style-type: none; margin: 0px; padding: 0px;" id="loc-' . $location . '">' . "\r\n";
		}
		foreach ($node->options['blocks'][$location] as $key => $module) {
			$aargs['key'] = $key;
			echo '<li class="upmodule" id="' . $node->node_id . '-' . $key . '-' . $location . '"><a href="' . 
			admin::get_callback(array('core', 'admin_node_modules'), $aargs) . '">';
			echo $module['extension'] . '::' . $module['module'] . '</a></li>' . "\r\n";
		}
		if ($all) {
			echo '</ul>' . "\r\n";
		
			echo '<input type="submit" name="submit" value="' . __('Add') . '" />';
			echo '</form>';
		}
	}
	
	function show_modform($location, $all = true) {
		global $node, $myargs;
		$fargs = $myargs;
		$fargs['mode'] = 'module';
		$fargs['location'] = $location;
		$aargs = $fargs;
		$aargs['mode'] = 'args';
		if ($all) {
			echo '<form method="post" action="' . admin::get_callback(array('core', 'admin_node_modules'), $fargs) . '">';
			echo '<ul style="list-style-type: none; margin: 0px; padding: 0px;" id="loc-' . $location . '">' . "\r\n";
		}
		foreach ($node->revision->modules[$location] as $key => $module) {
			$aargs['key'] = $key;
			echo '<li class="upmodule" id="' . $node->node_id . '-' . $key . '-' . $location . '"><a href="' . 
			admin::get_callback(array('core', 'admin_node_modules'), $aargs) . '">';
			echo $module['extension'] . '::' . $module['module'] . '</a></li>' . "\r\n";
		}
		if ($all) {
			echo '</ul>' . "\r\n";
		
			echo '<input type="submit" name="submit" value="' . __('Add') . '" />';
			echo '</form>';
		}
	}
	
	function admin_node_revisions($args) {
		$node_id = (isset($args['node'])) ? intval($args['node']) : intval($_POST['node']);
		$node = new CMS_Node();
		$node->node_id = $node_id;
		$node->read();
		$page = page::getnew(false);
		
		$page->sitenode = $page->get_this_site();
		
		$db = database::getnew();
		$sql = 'SELECT * 
				FROM ' . NODE_REVISIONS_TABLE . '
				WHERE node_id = ' . $node->node_id . '
				ORDER BY revision_date DESC';
		$result = $db->sql_query($sql);
		$rowset = $db->sql_fetchrowset($result);
		?>
		<h1><?php echo sprintf(__('View older versions of %s'), $node->title); ?></h1>
		<ul>
		<?php
		foreach ($rowset as $row) {
			//echo '<li><a href="../index.php?id=' . $node->node_id . '&amp;revision=' . $row['revision_number'] . '">';
			echo '<li><a href="' . $page->get_link($node, '/revision/' . $row['revision_number'], true) . '" class="external">';
			echo 'Revision ' . $row['revision_number'] . ' (' . date('d-m-Y G:i:s', $row['revision_date']) . ' )';
			echo '</a></li>' . "\r\n";
		}
		?>
		</ul>
		<?php
	}

	function admin_config($args)
	{
		global $config;
		$mode = (isset($args['mode'])) ? $args['mode'] : '';
		$do = (isset($args['do'])) ? $args['do'] : 'show';
		
		switch ($mode) {
			case 'performance':
				$forms = array(
					__('Page caching') => array(
						'caching_type' => array(
						'type'			=> 'radio',
						'name'			=> 'caching_type',
						'title'			=> __('Caching type'),
						'description'	=> __('The \'simple\' cache option is the most safe option, and is currently the recommended one. The \'normal\' option will cache all page output, except for pages with <em>dynamic modules</em>. Dynamic blocks will still be cached, so minor side-effects may occur. The \'aggressive\' cache option will cache every page in the front end, until it expires or is modified.'),
						'value'			=> array(
							'simple' => array(
								'title' => 'Simple (most safe)',
								'selected' => ($config['caching_type'] == 'simple' || empty($config['caching_type']))
							),
							'normal' => array(
								'title' => 'Normal (minor side-effects with blocks)',
								'selected' => ($config['caching_type'] == 'normal')
							),
							'aggressive' => array(
								'title' => 'Aggressive (can cause problems)',
								'selected' => ($config['caching_type'] == 'aggressive')
							)
						)
					),
					'caching_time' => array(
						'type'			=> 'selectbox',
						'name'			=> 'caching_time',
						'title'			=> __('Output cache time'),
						'description'	=> __('The time the cache keeps existing if there are no changes.'),
						'value'			=> utils::get_hours($config['caching_time'])
					)
				)
			);
			break;
			default:
				if ($do == 'show') {
					$do = 'nothing';
					echo __('Select a configuration page in the menu to your left.');
				}
			break;
		}
	
		switch ($do) {
			case 'save':
				foreach ($_POST as $key => $value) {
					utils::set_config($key, $value);
				}
				echo __('Settings are saved.');
			break;
			case 'show':
				$form = utils::load_extension('form');
				foreach ($forms as $title => $frm) {
					$form->action = admin::get_callback(array('core', 'admin_config'), $args);
					$form->submit = __('Save');
					$form->setformfields($frm);
					$form->title = $title;
					$form->generateform();
					echo $form->content;
				}
			break;
		}
	}
	
	function admin_node_options($args) {
		global $node, $easy, $options;
		
		$mode = isset($args['mode']) ? $args['mode'] : 'form';
		$easy = (isset($_POST['easy']) || isset($args['easy']));
		if(!isset($args['node']) && !isset($args['node'])) {
			exit;
		}
		$node_id = (isset($args['node'])) ? intval($args['node']) : intval($_POST['node']);
		$node = new CMS_Node();
		$node->node_id = $node_id;
		$node->read();
		
		$options = utils::run_hook_all('options_' . $node->type, $node->options);
		if(in_array($node->type, array('site', 'page', 'newsfolder'))) {
			$options = array_merge($options, array(
				'template' => array(
						'type'			=> 'template',
						'name'			=> 'template',
						'title' 		=> __('Template'),
		 	            'description'	=> __('The template that will be used for this node, and child nodes. Leave empty to use the parent\'s template.'),
						'value'			=> $node->options['template']
				)
			));
		}
		
		include(ROOT_PATH . 'extensions/core/node_options_form.php');
		
		ob_start();
		
		switch($mode) {
			case 'form':
			default:
				if (!$easy) {
					$title = sprintf(__('Edit options for %s'), $node->title);
				} else {
					$title = sprintf(__('Content wizard, step %d of %d'), 3, 4);	
				}
				$form = new node_options_form;
				$form->elements = array(
					$title => $options
				);
				$form->elements[$title]['node'] = array(
					'type'			=> 'hidden',
					'name'			=> 'node',
					'value'			=> $node->node_id,
					'raw'			=> true
				);
				if ($easy) {
					$form->elements[$title]['easy'] = array(
						'type'			=> 'hidden',
						'name'			=> 'easy',
						'value'			=> 'true',
						'raw'			=> true
					);
				}
				$form->form_id = 'node_options_form';
				$form->action = admin::get_callback(array('core', 'admin_node_options'), $args);
				$api = new formapi;
				echo $api->get_form($form);
			break;
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
			case 'initial':
				$type_options	= utils::run_hook_all('list_types');
				
				foreach ($type_options as $codename => $type) {
					if (!$type['allow_easy']) {
						continue;
					}
					
					$tempnode = new CMS_Node();
					$tempnode->type = $codename;
					$show = utils::display_allowed('this_under_other', $tempnode, $parent);
					unset($tempnode);
					if (!$show) {
						continue;
					}
					$thisargs = $args;
					unset($thisargs['mode']);
					$thisargs['type'] = $type['extension'] . '::' . $codename;
					?> 
					<p class="icon_p">
					<a href="<?php echo admin::get_callback(array('core', 'admin_node_add'), $thisargs) ?>">
					<img src="../<?php echo 'extensions/' . $type['extension'] . '/big-' . $codename . '.png' ?>" /><br /><?php echo $type['title'] ?></a><br /><?php echo $type['description'] ?></p>
					<?php
				}
			break;
			
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
				if (!$easy && !isset($args['type']) && $do != 'edit') {
					$values = array('' => array(
						'title' => '--' . __('Select') . '--',
						'selected' => false
					));
					
					foreach($type_options as $type => $extension) {
						$tempnode = new CMS_Node();
						$tempnode->type = $type;
						//$ext = utils::load_extension($extension['extension']);
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
				} else if (isset($args['type'])) {
					$type = explode('::', $args['type']);
					$type = $type[1];

					$form->elements[$title]['type'] = array(
						'name' => 'type',
						'type' => 'hidden',
						'value' => $type
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
						$.get('<?php echo utils::base() ?>adm/ajax.php?mode=cleantitle&title=' + escape($('#node_add_form_title').attr('value')), '', function(data, textStatus) {
							$('#node_add_form_title_clean').attr('value', data);
						});
					});
				</script>
				
				<?php
			break;
		}
	}
	
	function admin_node_delete($args) {
		$mode = isset($args['mode']) ? $args['mode'] : 'confirm';
		$node_id = isset($args['node']) ? $args['node'] : $_POST['node_id'];
		$db = database::getnew();
		
		switch($mode) {
			case 'confirmed':
				$post_vars = array('node_id');
				foreach($post_vars as $postvar) {
					if(empty($_POST[$postvar])) {
						trigger_error(__($postvar . '  not given!'), E_USER_ERROR);
						return;
					}
					$var = 'newnode_' . $postvar;
					$$var = $db->sql_escape($_POST[$postvar]); 
				}
				// Get the number of sites
				$sql = "SELECT COUNT(type) AS site_count
						FROM " . NODES_TABLE . "
						WHERE type = 'site'";
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$site_count = $row['site_count'];
				if($site_count < 1 || $site_count == 1) {
					define('SITE_DELETE_LEGAL', false);
				}
				else {
					define('SITE_DELETE_LEGAL', true);
				}
				$node = CMS_Node::getnew();
				$node->node_id = $node_id;
				$node->read();		
				if($node->type == 'site' && !SITE_DELETE_LEGAL) {
					echo __("Can't delete the node. Are you trying to delete a site?");
					define('ERROR', true);
				}
				else {
					$this->delete_node($node_id);
				}
		
				if(!defined('ERROR')) {
					echo 'reload';		
				}
			break;
				
			default:
			case 'confirm':
				$thisargs = $args;
				$thisargs['mode'] = 'confirmed';
				?>
				<form action="<?php echo admin::get_callback(array('core', 'admin_node_delete'), $thisargs) ?>" method="post">
					<div style="color: red;"><?php echo __("Are you sure you want to delete this node? This will remove any data and children."); ?></div>
					<input type="hidden" name="node_id" value="<?php echo $node_id; ?>" />
					<input type="submit" name="submit" value="<?php echo __("Submit"); ?>" />
				</form>
				<?php
			break;
		}
	}
		
	function delete_node($node_id) {
		global $db;
		$sql = 'DELETE FROM ' . NODES_TABLE . "
				WHERE node_id = $node_id";
		if(!$db->sql_query($sql)) {
			return false;
		}
		$sql = 'DELETE FROM ' . NODE_REVISIONS_TABLE . "
				WHERE node_id = $node_id";
		if(!$db->sql_query($sql)) {
			return false;
		}
		$sql = 'SELECT node_id FROM ' . NODES_TABLE . "
				WHERE parent_id = $node_id";
		if(!$result = $db->sql_query($sql)) {
			return false;
		}
		$affected_rows = $db->sql_affectedrows();
		if($affected_rows < 1) {
			$rowset = $db->sql_fetchrowset($result);
			foreach($rowset as $row) {
				$this->delete_node($row['node_id']);
			}
		}
		elseif($affected_rows == 1) {
			$row = $db->sql_fetchrow($result);
			$this->delete_node($row['node_id']);
		}
			
		return true;
	}
	
	function admin_left_site_structure() {
		echo '<ul class="nodes" id="tree" style="display: block;">';
		echo $this->get_admin_tree();
		echo '</ul>';
	}
	
	function admin_left_site_content() {
		$this->admin_left_site_structure();
	}
	
	function admin_left_site_config()
	{
		echo '<ul class="nodes" style="display: block;"><li><a href="admin_config.php?mode=performance" class="page">' .  __('Performance') . '</a></li></ul>';
	}
	
	
	function admin_get_default()
	{
		return array(
			'site_structure'	=> array(
				'extension' 	=> 'core',
			),
			'site_config'		=> array(
				'extension'		=> 'core',
			),
			'site_content'		=> array(
				'extension'		=> 'core',
			),
			'logout'			=> array(
				'extension'		=> 'core',
			),
		);
	}
	
	static function admin_default_site_structure()
	{
		echo __('To edit the site structure, select a node to edit on the left. To add a new node, click the Add button below a node.');
	}
	
	static function admin_default_site_config()
	{
		echo  __('Select a configuration page in the menu to your left.');
	}
	
	static function admin_default_site_content()
	{
		echo __('In this menu, you can manage the content of your nodes. To begin, select a node on the left.');
	}
	
	static function admin_default_logout()
	{
		global $user;
		$user->logout();
		echo '
		<script type="text/javascript">
			location.href = "login.php";
		</script>';
	}
	
	static function admin_left_viewsite()
	{
		echo '
		<script type="text/javascript">
			location.href = "' . utils::base() . '";
		</script>';
	}
}
?>