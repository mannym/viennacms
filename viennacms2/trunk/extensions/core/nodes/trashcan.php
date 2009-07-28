<?php
class TrashCanNode extends Node {
	public $has_modules = false;
	public $has_revision = false;
	public $is_legacy = false;
	
	public function get_typedata() {
		return array(
				'extension' => 'core',
				'title' => __('Trash can'),
				'description' => '',
				'type' => 'none',
				'icon' => '~/blueprint/views/admin/images/icons/trashcan.png',
				'options' => array(),
				'display_callback' => 'none'
			);
	}
	
	public function display($arguments) {
		die();
	}
}