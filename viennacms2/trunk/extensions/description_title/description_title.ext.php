<?php
class extension_description_title {
	public function __construct() {
		VEvents::register('core.alter-layout-view', array($this, 'add_title'));
	}
	
	public function add_title($view) {
		$title = $view['title'];
		
		if (cms::$vars['node']->node_id == (string)cms::$vars['sitenode']->options['homepage']) {
			$view['title'] = cms::$vars['sitenode']->description;
		}
	}
}
?>