<?php
class GalleryNode extends Node {
	public $has_modules = false;
	public $has_revision = false;
	public $is_legacy = false;
	
	public function get_typedata() {
		return array(
				'extension' => 'pictureviewer',
				'title' => __('Picture gallery'),
				'description' => __('Shows a folder with pictures in a gallery.'),
				'type' => 'none',
				'icon' => '~/blueprint/views/admin/images/icons/page.png',
				'big_icon' => '~/blueprint/views/admin/images/icons/page_big.png',
				'options' => array(
					'folder' => array(
						'label' => __('Folder'),
						'description' => __('The ID of the folder to display.'),
						'type' => 'textbox',
						'required' => true
					)
				),
				//'display_callback' => array($this, 'pictureviewer'),
			);
	}
	
	public function display($arguments) {
		$mode = ($arguments[0]) ? $arguments[0] : 'folder';

		switch ($mode) {
			case 'folder':
				ob_start();

				$folder_id = ($arguments[1]) ? $arguments[1] : $this->options['folder'];
				
				$getter = new Node();
				$getter->parent = (string)$folder_id;
				$getter->type = 'file';
				$files = $getter->read();
				
				$getter->type = 'filesfolder';
				$folders = $getter->read();
				
				$folder = new Node();
				$folder->node_id = (string)$folder_id;
				$folder->read(true);
				
				if ($folder_id != (string)$this->options['folder']) {
					echo '<img src="' . manager::base() . 'blueprint/views/admin/images/icons/folder.png" alt="" /> <a href="' . view::url($this, 'folder/' . $folder->get_parent()->node_id) . '">' . __('Up') . '</a><br />';
				}
				
				foreach ($folders as $folder) {
					$files_in_folder = $folder->get_children();
					$image_count = 0;
					
					foreach ($files_in_folder as $file) {
						if ($file->type == 'filesfolder' || substr($file->options['mimetype'], 0, 6) == 'image/') {
							$image_count++;
						}
					}
					
					if ($image_count == 0) {
						continue;
					}
					
					echo '<img src="' . cms::base() . 'blueprint/views/admin/images/icons/folder.png" alt="" /> <a href="' . view::url($this, 'folder/' . $folder->node_id) . '">' . $folder->title . ' (' . $image_count . ')</a><br />';
				}
				
				foreach ($files as $file) {
					if (substr($file->options['mimetype'], 0, 6) == 'image/') {
						echo '<div style="float: left; text-align: center; width: 190px; height: 170px; margin: 5px;"><a href="' . view::url($this, 'photo/' . $file->node_id) . '">';
						echo '<img src="' . view::url($this, 'image/160/' . $file->node_id) . '" alt="' . $file->title . '" /></a><br /><!--' . $file->title . '--></div>';
					}
				}

				echo '<br style="clear: both;" />';
				
				$output = ob_get_contents();
				ob_end_clean();
				
				return $output;
			break;
			case 'photo':
				$file = new Node();
				$file->node_id = $arguments[1];
				$file->read(true);
				
				if ($file->type != 'file' || substr($file->options['mimetype'], 0, 6) != 'image/') {
					return;
				}
				
				ob_start();
				
				echo '<div style="text-align: center;">';
				echo '<a href="' . view::url($file->to_url()) . '">';
				echo '<img src="' . view::url($this, 'image/640/' . $file->node_id) . '" alt="' . $file->title . '" />';
				echo '</a>';
				
				echo '<br /><a href="' . view::url($this, 'folder/' . $file->get_parent()->node_id) . '">&laquo; ' . sprintf(__('Back to %s'), $file->get_parent()->title) . '</a>';

				echo '</div>';

				echo '<div class="photo-nav-links">';

				$previous = $file->previous();

				while ($previous->type != 'file' || substr($previous->options['mimetype'], 0, 6) != 'image/') {
					if (!$previous) {
						break;
					}

					$previous = $previous->previous();
				}

				if ($previous) {
					echo '<div style="float: left; width: 49%">';
					echo view::link(__('&laquo; Previous'), $this, array('args' => 'photo/' . $previous->node_id));
					echo '</div>';
				}

				$next = $file->next();

				while ($next->type != 'file' || substr($next->options['mimetype'], 0, 6) != 'image/') {
					if (!$next) {
						break;
					}

					$next = $next->next();
				}

				if ($next) {
					echo '<div style="float: right; text-align: right; width: 49%">';
					echo view::link(__('Next &raquo;'), $this, array('args' => 'photo/' . $next->node_id));
					echo '</div>';
				}

				echo '<br style="clear: both;" /></div>';
				
				$output = ob_get_contents();
				ob_end_clean();
				
				return $output;
			break;
			case 'image':
				$max_size = $arguments[1];
				
				$file = new Node();
				$file->node_id = $arguments[2];
				$file->read(true);
				
				if ($file->type != 'file') {
					return false;
				}
				
				@mkdir(ROOT_PATH . 'cache/pictureviewer/', 777);
				
				$filename = ROOT_PATH . $file->description;
				$thumbnail_name = ROOT_PATH . 'cache/pictureviewer/' . str_replace('.upload', '.thumb-' . intval($max_size), str_replace('files/', '', $file->description));
				
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
				
				return false;
			break;
		}
	}
}