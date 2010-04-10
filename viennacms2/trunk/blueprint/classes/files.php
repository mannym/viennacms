<?php
class Files {
	public $fileroot;
	
	public function init() {
		$node = new Node();
		$node->parent = 0;
		$node->type = 'filesfolder';
		$node->read(true);
		
		if (empty($node->title)) {
			$node = Node::create('Node');
			$node->parent = 0;
			$node->type = 'filesfolder';
			__('Files'); // dummy for poedit
			$node->title = 'Files.localized';
			$node->write();
		}

		$this->fileroot = $node;
	}
	
	public function get_file_root() {
		return $this->fileroot;
	}
	
	public function get_file_widget($file) {
		$output = new stdClass;
		$output->output = '';
		$output->append = '';
		
		VEvents::invoke('files.file-widget', $file, $output);
		
		if ($output->output == '') {
			VEvents::invoke('files.default-widget', $file, $output);
		}
		
		return $output->output . $output->append;
	}
	
	public function picture($file, $max_size = 640) {
		@mkdir(VIENNACMS_PATH . 'cache/pictureviewer/', 777);
				
		$filename = VIENNACMS_PATH . $file->description;
		$thumbnail_name = VIENNACMS_PATH . 'cache/pictureviewer/' . str_replace('.upload', '.thumb-' . intval($max_size), str_replace('files/', '', $file->description));
		
		if (!file_exists($thumbnail_name)) {
			$type = substr($file->options['mimetype'], 6);
			
			// Set a maximum height and width
			$width = $max_size;
			$height = $max_size;
			
			// Get new dimensions
			list($width_orig, $height_orig) = @getimagesize($filename);
			
			if ($width_orig < $width && $height_orig < $height) {
				$width = $width_orig;
				$height = $height_orig;
			}
			
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
		
		header('Content-type: ' . $file->options['mimetype']);
		readfile($thumbnail_name);
	}
}
