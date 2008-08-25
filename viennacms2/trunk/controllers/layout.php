<?php
class LayoutController extends Controller {
	public function page($content) {
		$this->view['content'] = $content;
		$this->view['styles'] = $this->get_styles();
	}
	
	private function get_styles() {
		$styles = array(
			'layouts/' . $this->global['style'] . '/stylesheet.css'
		);
		$return = '';
		
		foreach ($styles as $style) {
			$return .= '<link href="' . $style . '" rel="stylesheet" type="text/css" />';
		}
		
		return $return;
	}
}
