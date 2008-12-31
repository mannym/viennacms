<?php
/**
* Base plugin
* Implements many plugin features for the base system.
* not used currently, waiting for better implementation of phpBB's plugin system
*/

class cms_plugin_core_info {
	public $name = 'Base plugin';
	public $description = 'Implements core features';
	public $author = 'viennaCMS developers';
	public $version = '2.0';
	
	public $objects = array('cms_plugin_core');
	
	public function setup_plugin($object) {
		
	}
}
