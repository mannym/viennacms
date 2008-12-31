<?php
class AdminNodesPaneController extends Controller {
	function main() {
		$this->view['images_path'] = manager::base() . 'blueprint/views/admin/images';
		$this->view['tree_data'] = cms::get_admin_tree($this->view->url('admin/controller/node/edit/%node_id'));
		
		$this->view['toolbar'] = AdminController::add_toolbar(array(
		'add' => array(
			'icon' => $this->view['images_path'] . '/icons/add.png',
			'callback' => 'admin/controller/node/add/%selected_id',
			'type' => 'submenu'
			)
		), $this);
	}
}
