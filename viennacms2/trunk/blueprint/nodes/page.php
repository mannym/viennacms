<?php
// register PageNode with the CMS base

//cms::$helpers->register_node_type('page');

/**
 * PageNode
 * 
 * implements a static node type for the basic 'Page' node type.
 */
class PageNode extends StaticNode {
	static public function get_typedata() {
		return array(
			'extension' => 'core',
			'title' => __('Page'),
			'description' => __('A page is a simple way of posting content that almost never changes.'),
			'type' => 'static',
			'icon' => '~/blueprint/views/admin/images/icons/page.png',
			'options' => array()
		);
	}
}