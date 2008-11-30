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
				'icon' => '~/views/admin/images/icons/page.png',
				'big_icon' => '~/views/admin/images/icons/page_big.png',
				'options' => array()
			),
			'dynamicpage' => array(
				'extension' => 'core',
				'title' => __('Dynamic page'),
				'description' => __('A dynamic page is used for placing modules on a site. These modules can be used for all kinds of dynamic content.'),
				'type' => 'dynamic',
				'icon' => '~/views/admin/images/icons/dynamicpage.png',
				'big_icon' => '~/views/admin/images/icons/dynamicpage_big.png',
				'options' => array()
			),
			'site' => array(
				// let's not go there... for now :)
				'icon' => '~/views/admin/images/icons/site.png',
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
	
	
	public function module_manifest() {
		return array(
				'htmlcontent' => array(
					'icon' => '~/views/admin/images/icons/page.png',
					'title' => __('HTML content'),
					'description' => __('A module which provides a content box which renders HTML.')
					)	
				);
	}
	
	function display_allowed($type, $node, $other) {
		switch ($type) {
			case 'this_under_other':
				if ($node->type == 'site') {
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
}
