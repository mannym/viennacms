<?php
class AdminNodesPaneController extends Controller {
	function main() {
		$parts = explode('/', cms::$router->query);
		$id = array_pop($parts);
		
		$this->view['images_path'] = manager::base() . 'blueprint/views/admin/images';
		$this->view['tree_data'] = cms::get_admin_tree($this->view->url('admin/controller/node/edit/%node_id'), $id);
		
		if (!empty($id) && is_numeric($id)) {
			$this->view['toolbar'] = AdminController::add_toolbar(array(
			'add' => array(
				'icon' => $this->view['images_path'] . '/icons/add.png',
				'callback' => 'admin/controller/node/add/' . $id, // hacky way...
				'type' => 'submenu'
				)
			), $this);
		}
	}
}
