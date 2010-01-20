<?php
class extension_pictureviewer {
	public function __construct() {
		cms::$registry->register_type('GalleryNode');
	}
}
