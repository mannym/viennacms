<?php
class TranslationSetNode extends Node {
	public $has_modules = false;
	public $has_revision = false;
	public $is_legacy = false;
	
	public function get_typedata() {
		return array(
			'icon' => '~/blueprint/views/admin/images/icons/dynamicpage.png',
		);
	}
}