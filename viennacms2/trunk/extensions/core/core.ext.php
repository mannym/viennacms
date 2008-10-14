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
				'icon' => '~/views/admin/images/icons/page.png'
			),
			'dynamicpage' => array(
				'extension' => 'core',
				'title' => __('Dynamic page'),
				'description' => __('A dynamic page is used for placing modules on a site. These modules can be used for all kinds of dynamic content.'),
				'type' => 'dynamic',
				'icon' => '~/views/admin/images/icons/dynamicpage.png'
			),
			'site' => array(
				// let's not go there... for now :)
				'icon' => '~/views/admin/images/icons/site.png'
			)
		);
	}
}
