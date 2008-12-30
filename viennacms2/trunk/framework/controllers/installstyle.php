<?php
class InstallStyleController extends Controller {
	public function __construct() {
		View::$searchpaths['blueprint/layouts/minimal/'] = VIEW_PRIORITY_USER;
	}
	
	public function page($content) {
		$this->view['styles'] = '<link rel="stylesheet" href="' . manager::base() . 'layouts/default/stylesheet.css" /><link rel="stylesheet" href="' . manager::base() . 'framework/views/system/form.css" />';
		$this->view['content'] = $content;
	}
}
