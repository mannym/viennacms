<?php
class FilesfolderNode extends Node {
	public $has_modules = false;
	public $has_revision = false;
	public $is_legacy = false;
	
	public function get_typedata() {
		return array(
				'extension' => 'core',
				'title' => __('Folder'),
				'description' => '',
				'type' => 'none',
				'icon' => '~/blueprint/views/admin/images/icons/folder.png',
				'options' => array(),
			);
	}
	
	public function display($arguments) {
		exit;
	}
}
