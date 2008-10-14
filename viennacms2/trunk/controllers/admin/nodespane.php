<?php
class AdminNodesPaneController extends Controller {
	function main() {
		$this->view['images_path'] = manager::base() . 'views/admin/images';
		$this->view['tree_data'] = cms::get_admin_tree($this->view->url('admin/node/edit/%node_id'));
	}
}
