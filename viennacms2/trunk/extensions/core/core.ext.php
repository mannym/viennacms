<?php
/**
* viennaCMS2 core extension
* 
* @package viennaCMS2
* @version $Id$
* @copyright (c) 2008 viennaCMS group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

class extension_core {
	public function get_node_types() {
		return array(
			'page' => array(
				'extension' => 'core',
				'title' => __('Page'),
				'description' => __('A page is a simple way of posting content that almost never changes.'),
				'type' => 'static',
				'icon' => '~/blueprint/views/admin/images/icons/page.png',
				'big_icon' => '~/blueprint/views/admin/images/icons/page_big.png',
				'options' => array()
			),
			'dynamicpage' => array(
				'extension' => 'core',
				'title' => __('Dynamic page'),
				'description' => __('A dynamic page is used for placing modules on a site. These modules can be used for all kinds of dynamic content.'),
				'type' => 'dynamic',
				'icon' => '~/blueprint/views/admin/images/icons/dynamicpage.png',
				'big_icon' => '~/blueprint/views/admin/images/icons/dynamicpage_big.png',
				'options' => array()
			),
			'filesfolder' => array(
				'extension' => 'core',
				'title' => __('Folder'),
				'description' => '',
				'type' => 'none',
				'icon' => '~/blueprint/views/admin/images/icons/folder.png',
				'options' => array(),
				'display_callback' => 'none'
			),
			'file' => array(
				'extension' => 'core',
				'title' => __('File'),
				'description' => '',
				'type' => 'none',
				'icon' => '~/blueprint/views/admin/images/icons/file.png',
				'options' => array(),
				'display_callback' => array($this, 'output_file'),
				'path_callback' => array($this, 'file_path')
			),
			'site' => array(
				// let's not go there... for now :)
				'icon' => '~/blueprint/views/admin/images/icons/site.png',
				'type' => 'dynamic', // somewhat, this is a special case
				'options' => array(
					'404_url' => array(
						'label' => __('"Page not found" URL'),
						'description' => __('The URL on the site, which will be redirected to when a page can not be found.'),
						'type' => 'textbox',
						'required' => false,
						'validate_function' => array($this, 'validate_url')
					),
					'homepage' => array(
						'label' => __('Home page'),
						'description' => __('The ID of the node, which will be set as the home page for this site.'),
						'type' => 'textbox',
						'required' => true
					)
				)
			)
		);
	}
	
	public function core_get_admin_tree($op, &$url, $template, $node) {
		if ($op == 'admin_tree') {
			if ($node->type == 'filesfolder') {
				$url->url = 'admin/controller/file/folder/' . $node->node_id;
			}
		
			if ($node->type == 'file') {
				$url->url = 'admin/controller/file/file/' . $node->node_id;
			}
		}
	}
	
	public function core_admin_node_add_url(&$url, $type, $parent) {
		if ($type == 'file') {
			$url->url = 'admin/controller/file/upload/' . $parent->node_id;
		}
	}

	public function node_toolbar($node) {
		$toolbar = array();

		$toolbar[__('New')] = array(
			'icon' => manager::base() . 'blueprint/views/admin/images/icons/add.png',
			'callback' => 'admin/controller/node/add/' . $node->node_id,
			'type' => 'submenu'
		);

		if ($node->type == 'site') {
			$toolbar[__('Themes')] = array(
				'icon' => manager::base() . 'extensions/core/icons/theme.png',
				'callback' => 'admin/controller/themes/select/' . $node->node_id
			);
		}

		return $toolbar;
	}

	public function node_edit_widgets($node) {
		$widgets = array();

		if ($node->typedata['type'] == 'static') {
			$preview_template = View::url('admin/controller/file/editor_widget/%node_id');

			$tree_options = array(
				'node' => cms::$files->fileroot,
				'url' => cms::$router->query . '#',
				'url_from' => 'admin',
				'url_attributes' => ' onclick="file_add_to_manager(%node_id, \'%node_type\', \'' . $preview_template . '\'); return false;"'
			);

			$output = '';
			$output .= __('Add a file to your content by selecting it here.') . '<ul class="treeview">';
			$output .= cms::$helpers->get_tree($tree_options);
			$output .= '</ul>';

			$output .= '<script type="text/javascript" src="' . manager::base() . 'extensions/core/file-add.js"></script>';

			$widgets['file-admin-pane'] = array(
				'title' => __('Files'),
				'content' => $output,
			);
		}

		return $widgets;
	}
	
	public function node_edit_pre_load($node) {
		$content = preg_replace_callback('@<viennacms:file node="(.+?)">.*?</viennacms:file>@', array($this, 'file_tag_refresh'), $node->revision->content);
		$node->revision->content = $content;
	}
	
	public function node_show_alter($node) {
		$content = preg_replace_callback('@<viennacms:file node="(.+?)">.*?</viennacms:file>@', array($this, 'file_tag_replace'), $node->revision->content);
		$node->revision->content = $content;
	}
	
	public function file_tag_refresh($regs) {
		$file = new Node();
		$file->node_id = $regs[1];
		$file->cache = 1800;
		$file->read(true);
		
		$output = cms::$files->get_file_widget($file);
		
		return '<viennacms:file node="' . $regs[1] . '">' . $output . '</viennacms:file>';
	}
	
	public function file_tag_replace($regs) {
		$file = new Node();
		$file->node_id = $regs[1];
		$file->cache = 1800;
		$file->read(true);
		
		return cms::$files->get_file_widget($file);
	}
	
	public function output_file($node) {
		$original_mime = $node->options['mimetype'];
		$mimetype = $original_mime;
		$filename = ROOT_PATH . $node->description;
		
		if (isset($_GET['thumbnail'])) {
			$filename = str_replace('.upload', '.thumb', $filename);
		}
		
		$magic = false;
		
		if (function_exists('finfo_open')) { // PHP 5.3/PECL fileinfo
			$finfo = @finfo_open(FILEINFO_MIME);
			
			if ($finfo) {
				$mimetype = finfo_file($finfo, $filename);
				finfo_close($finfo);
				
				$magic = true;
			}
		}
		
		if ($magic == false && function_exists('mime_content_type')) { // PHP 5.2 with default and mime file
			if (ini_get('mime_magic.magicfile')) {
				$mimetype = mime_content_type($filename);
				$magic = true;
			}
		}
		
		if ($mimetype === false) {
			$mimetype = $original_mime;
		}
		
		// TODO: make mime hooking modular :)
		
		header('Content-type: ' . $mimetype);
		if (substr($mimetype, 0, 6) != 'image/') {
			header('Content-Disposition: attachment; filename="' . $node->title . '"');
		}
		
		readfile($filename);
		
		$node->options['downloads'] = ((int)((string)$node->options['downloads'])) + 1;
		$node->write(true, false);
		
		return false;
	}
	
	public function file_path($node, $path) {
		$pathinfo = pathinfo($node->title);
		$name = $pathinfo['filename'];
		$extension = '.' . $pathinfo['extension'];
		
		$path->path = 'file/' . cms::$helpers->create_node_parent_path($node) . cms::$router->clean_title($name) . $extension;
	}
	
	public function core_file_widget($file, $output) {
		if (substr($file->options['mimetype'], 0, 6) == 'image/') {
			$type = substr($file->options['mimetype'], 6);
			
			if (extension_loaded('gd') && function_exists('imagecreatetruecolor')) {
				$filename = ROOT_PATH . $file->description;
				$thumbnail_name = str_replace('.upload', '.thumb', $filename);

				// Set a maximum height and width
				// TODO: make this configurable
				$width = 600;
				$height = 400;
				
				// Get new dimensions
				list($width_orig, $height_orig) = @getimagesize($filename);
				
				if (!file_exists($thumbnail_name) && ($width_orig > $width || $height_orig > $height)) {
					$ratio_orig = $width_orig/$height_orig;
					
					if ($width/$height > $ratio_orig) {
					   $width = $height*$ratio_orig;
					} else {
					   $height = $width/$ratio_orig;
					}
					
					// Resample
					$image_p = imagecreatetruecolor($width, $height);
					
					switch ($type) {
						case 'jpeg':
							$image = imagecreatefromjpeg($filename);
						break;
						case 'png':
							$image = imagecreatefrompng($filename);
						break;
						case 'gif':
							$image = imagecreatefromgif($filename);
						break;
					}
					
					imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
					
					switch ($type) {
						case 'png':
							imagealphablending($image_p, false);
							imagesavealpha($image_p, true);
							imagepng($image_p, $thumbnail_name);
						break;
						case 'jpeg':
							imagejpeg($image_p, $thumbnail_name, 80);
						break;
						case 'gif':
							// this didn't exist in old PHP/GD versions, though you can't call 5.2 old
							// thanks to stupid patent stuff -- though expiring is fine with me :)
							
							imagegif($image_p, $thumbnail_name);
						break;
					}
				}
				
				if (($width_orig > $width || $height_orig > $height)) {
					$output->output .= '<a href="' . View::url('node/show/' . $file->node_id) . '">';
					$output->output .= '<img src="' . View::url('node/show/' . $file->node_id) . '?thumbnail" alt="' . $file->title . '" />';
					$output->output .= '</a><br />';
					
					$output->append .= __('The image has been resized to fit, click the image to view full size.');
				} else {
					$output->output .= '<img src="' . View::url('node/show/' . $file->node_id) . '" alt="' . $file->title . '" />';
				}
			}
		}
	}
	
	public function core_file_default_widget($file, $output) {
		$view = new View();
		$view->path = 'default_file_widget.php';
		$view['file'] = $file;
		$view['url'] = $view->url('node/show/' . $file->node_id);
		$view['nice_size'] = cms::$helpers->readable_size((string) $file->options['size']);
		
		$output->output .= $view->display();
	}
	
	public function module_manifest() {
		return array(
				'htmlcontent' => array(
					'icon' => '~/blueprint/views/admin/images/icons/page.png',
					'title' => __('HTML content'),
					'description' => __('A module which provides a content box which renders HTML.')
					),
				'nicenav' => array(
					'icon' => '~/blueprint/views/admin/images/icons/nicenav.png',
					'title' => __('Nice sub-navigation'),
					'description' => __('Nice 2-column sub-navigation with descriptions.')
				)	
				);
	}
	
	function display_allowed($type, $node, $other) {
		switch ($type) {
			case 'this_under_other':
				if ($node->type == 'site') {
					return false;
				}
				
				if ($node->type == 'filesfolder' || $node->type == 'file') {
					if ($other->type != 'filesfolder') {
						return false;
					}
				}
				
				if ($other->type == 'file') {
					return false;
				}
				
				if ($other->type == 'filesfolder' && ($node->type != 'filesfolder' && $node->type != 'file')) {
					return false;
				}
			break;
		}
	}
	
	function validate_url($url) {
		if (!empty($url)) {
			$result = cms::$router->check_url_existence($url);

			if (!$result) {
				return __('The entered URL does not exist, or is not accessible.');
			}
			
			return false;
		}
	}
	
	function acp_metadata($node, $caller) {
		return array(
			'history' => array(
				'title' => __('Revisions'),
				'content' => $this->acp_node_revisions($node, $caller)
			)
		);
	}
	
	function acp_node_revisions($node, $caller) {
		$revisions = new Node_Revision();
		$revisions->node = $node->node_id;
		$revisions->order = array('time' => 'desc');
		$revisions = $revisions->read();
		
		$output = '';
		
		foreach ($revisions as $revision) {
			$output .= '<li><a class="page" href="' . $caller->view->url('admin/controller/revision/view/' . $node->node_id . '/' . $revision->number) . '">' . sprintf(__('Revision %d'), $revision->number) . '</a></li>';
		}
		
		return $output;
	}
	
	function acp_get_panes($view) { // jippeeee, more hooks
		switch ($view) {
			case 'nodes':
				return array(
					'left' => array(
						array(
							'title' => __('Nodes'),
							'href' => 'nodes'
						),
					)
				);
			break;
			case 'system':
				return array(
					'left' => array(
						array(
							'title' => __('System'),
							'href' => 'system'
						),
					)
				);
			break;
/*			case 'files':
				return array(
					'left' => array(
						array(
							'title' => __('Files'),
							'href' => 'files'
						),
					)
				);
			break;*/
		}
	}
	
	function acp_views() {
		return array(
			'nodes' => __('Nodes'),
			'system' => __('System'),
//			'files' => __('Files')
		);
	}
	
	function acp_system_pane() {
		return array(
			'extensions' => array(
				'title' => __('Extensions'),
				'icon' => '~/blueprint/views/admin/images/icons/file.png',
				'href' => 'admin/controller/extensions/index'
			)
		);
	}
	
	function retrieve_sidebar($location) {
		$content = '';
		
		// first, check for any 'sidebar' node modules
		if (!empty(cms::$vars['node'])) {
			// this is a node we're talking about... and nodes may have modules :)
			
			if (cms::$vars['node']->typedata['type'] == 'dynamic') {
				// especially if they're dynamic nodes.
				
				$modules = unserialize(cms::$vars['node']->revision->content);
				
				if (!empty($modules[$location])) {
					$content .= cms::$helpers->render_modules($modules[$location]);
				}
			}
		}
		
		// now, check if there is a sitenode
		// there may not be one, probably, better safe than sorry
		if (!empty(cms::$vars['sitenode'])) {
			// during implementation, I was thinking how to implement this...
			// would be easier on the admin side to simply handle it the same way as dynamic nodes, so we do it that way
			// note added later: admin-side implementation was thinking WHY I ADDED A REVISION... changed to options for more V1-ish stuff
			// oh, wait, V1 had in a option because the site had a revision with content. rethinking
			
			$modules = unserialize(cms::$vars['sitenode']->revision->content);
			
			if ($modules !== false && !empty($modules[$location])) {
				$content .= cms::$helpers->render_modules($modules[$location]);
			}
		}
		
		return $content;
	}
}
