<?php
class StaticNode extends Node {
	public $has_modules = false;
	public $has_revision = true;
	public $is_legacy = false;
	
	public function display($arguments) {
		if (count($arguments) != 0) {
			return cms::$manager->page_not_found();	
		}
		
		return $this->revision->content;
	}
}