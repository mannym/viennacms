<?php
class AdminThemesController extends Controller {
	public function select() {
		$node = new Node();
		$node->node_id = $this->arguments[0];
		$node->read(true);
		
		$themes = scandir(ROOT_PATH . 'layouts');
		
		foreach ($themes as $theme) {
			$dir = ROOT_PATH . 'layouts/' . $theme;
			
			if (file_exists($dir . '/info.php')) {
				include($dir . '/info.php');
				
				$fthemes[$theme] = $theme_info;
			}
		}
		
		$output = '';
		
		$current_theme = (string)$node->options['style'];
		
		if (empty($current_theme)) {
			$current_theme = 'default';
		}
		
		$output .= '<h2>' . __('Current theme') . '</h2>';
		$output .= $this->show_theme_thing($current_theme, $fthemes[$current_theme], true);
		
		$output .= '<h2 style="clear: both;">' . __('Available themes') . '</h2>';
		$output .= '<div style="margin-left: 5px; margin-right: 5px; border: 1px solid #ccc; border-right: none;">';
		
		foreach ($fthemes as $name => $theme) {
			$output .= $this->show_theme_thing($name, $theme, false, count($fthemes));
		}
		
		$output .= '<br style="clear: both;" />';
		$output .= '</div>';
		
		return $output;
	}
	
	public function choose() {
		if (!file_exists(ROOT_PATH . 'layouts/' . $this->arguments[0] . '/info.php')) {
			return __('This style does not exist correctly.');
		}
		
		$node = new Node();
		$node->parent = 0;
		$node->type = 'site';
		$node->read(true);
		$node->options['style'] = $this->arguments[0];
		$node->write();
		
		admincontroller::notify(sprintf(__('The current style has been set to %s.'), $this->arguments[0]));
		cms::redirect('admin/controller/themes/select');
	}
	
	private function show_theme_thing($name, $theme_info, $default = false, $count = 1) {
		if ($default) {
			$output = '<img src="' . manager::base() . 'layouts/' . $name . '/screenshot.png" style="width: 150px; height: 128px; float: left; margin-right: 5px;" alt="' . $theme_info['name'] . '" />';
			$output .= '<p><strong>' . $theme_info['name'] . ' ' . __('by') . ' <a href="' . $theme_info['url'] . '">' . $theme_info['author'] . '</a></strong></p>';
			$output .= '<p>' . $theme_info['description'] . '</p>';
			$output .= '<p>' . sprintf(__('All files for this theme are located in <code>%s</code>.'), '/layouts/' . $name) . '</p>';
		} else {
			$percentage = floor(99 / (min($count, 3)));
			
			$output = '<div class="available-theme" style="margin-right: 5px; float: left; width: ' . $percentage . '%; border-right: 1px solid #ccc;">';
			$output .= '<a class="thickbox" href="' . View::url('admin/controller/themes/choose/' . $name) . '">';
			$output .= '<img src="' . manager::base() . 'layouts/' . $name . '/screenshot.png" style="margin-bottom: 6px; border: 0px;" alt="' . $theme_info['name'] . '" />';
			$output .= '</a>';
			$output .= '<br /><a class="thickbox" href="' . View::url('admin/controller/themes/choose/' . $name) . '">';
			$output .= '<strong>' . $theme_info['name'] . '</strong>';
			$output .= '</a>';
			$output .= '<p>' . $theme_info['description'] . '</p>';
			$output .= '<div style="display: none;"><a class="previewlink" href="' . manager::base() . '?preview=' . $name . '&TB_iframe=true">pr</a><a class="activatelink" href="' . View::url('admin/controller/themes/choose/' . $name) . '">' . sprintf(__('Activate "%s"'), $theme_info['name']) . '</a></div>';
			$output .= '</div>';
		}
		
		return $output;
	}
}
