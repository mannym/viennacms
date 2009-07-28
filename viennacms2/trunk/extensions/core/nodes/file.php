<?php
class FileNode extends Node {
	public $has_modules = false;
	public $has_revision = false;
	public $is_legacy = false;
	
	public function get_typedata() {
		return array(
				'extension' => 'core',
				'title' => __('File'),
				'description' => '',
				'type' => 'none',
				'icon' => '~/blueprint/views/admin/images/icons/file.png',
				'options' => array(),
				//'display_callback' => array($this, 'output_file'),
				//'path_callback' => array($this, 'file_path')
			);
	}
	
	public function create_path($path) {
		$pathinfo = pathinfo($this->title);
		$name = $pathinfo['filename'];
		$extension = '.' . $pathinfo['extension'];
		
		$path->path = 'file/' . cms::$helpers->create_node_parent_path($this) . cms::$router->clean_title($name) . $extension;
	}
	
	public function display($arguments) {
		$this->open_readonly = false;
		
		$original_mime = $this->options['mimetype'];
		$mimetype = $original_mime;
		$filename = ROOT_PATH . $this->description;
		
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
			header('Content-Disposition: attachment; filename="' . $this->title . '"');
		}
		
		readfile($filename);
		
		$this->options['downloads'] = ((int)((string)$this->options['downloads'])) + 1;
		$this->write(true, false);
		
		return false;
	}
}