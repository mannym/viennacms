<?php
class StaticNode extends Node {
	public $has_modules = false;
	public $has_revision = true;
	public $is_legacy = false;
	
	public function display($arguments) {
		return $this->revision->content;
	}
}