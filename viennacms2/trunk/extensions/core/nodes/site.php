<?php
class SiteNode extends Node {
	public $has_modules = true;
	public $has_revision = true;
	public $is_legacy = false;
	
	public function get_typedata() {
		return array(
				// let's not go there... for now :)
				'icon' => '~/blueprint/views/admin/images/icons/site.png',
				'type' => 'dynamic', // somewhat, this is a special case
				'options' => array(
					'404_url' => array(
						'label' => __('"Page not found" URL'),
						'description' => __('The URL on the site, which will be redirected to when a page can not be found.'),
						'type' => 'textbox',
						'required' => false,
						'validate_function' => array(cms::ext('core'), 'validate_url')
					),
					'homepage' => array(
						'label' => __('Home page'),
						'description' => __('The ID of the node, which will be set as the home page for this site.'),
						'type' => 'textbox',
						'required' => true
					)
				)
			);
	}
	
	public function display($arguments) {
		die();
	}
}

