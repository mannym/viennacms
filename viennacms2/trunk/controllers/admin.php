<?php
class AdminController extends Controller {
	private function check_auth() {
		if (!cms::$user->logged_in) {
			cms::$vars['error_title'] = __('Authentication required');
			trigger_error(__('You need to log in to access the ACP.'));
		}
		
		$auth = new Auth();
		$rights = $auth->get_rights('admin:see_acp', cms::$user->user);
		
		if (!in_array('y', $rights)) {
			cms::$vars['error_title'] = __('Information');
			trigger_error(__('You are not allowed to access the Administration Control Panel.'));
		}		
	}
	
	public function main() {
		$this->check_auth();
		$this->view['pane_url'] = $this->view->url('admin/panes');
		
		$node_types = manager::run_hook_all('get_node_types');
		$icons = array();
		foreach ($node_types as $id => $data) {
			$icons[$id] = str_replace('~/', manager::base(), $data['icon']);
		}
		
		$this->view['icons'] = $icons;
		
		return CONTROLLER_NO_LAYOUT;
	}
	
	public function controller() {
		$this->check_auth();
		
		$controllern = array_shift($this->arguments);
		$method = array_shift($this->arguments);
		
		$controller = cms::$manager->get_controller('admin/' . $controllern); // array_shift to remove the original argument.
		$controller->view = new View();
		$controller->view->path = 'admin/' . $controllern . '/' . $method . '.php';
		$controller->arguments = $this->arguments;
		$controller->$method();
		echo $controller->view->display();
		
		exit;
	}
	
	public function panes() {
		$this->check_auth();
		echo json_encode($this->get_panes());
		exit;
	}
	
	public function pane() {
		$pane = array_shift($this->arguments);
		
		$controller = cms::$manager->get_controller('admin/' . $pane . 'pane'); // array_shift to remove the original argument.
		$controller->view = new View();
		$controller->view->path = 'admin/panes/' . $pane . '.php';
		$controller->arguments = $this->arguments;
		$controller->main();
		echo $controller->view->display();
		exit;
	}
	
	private function get_panes() {
		// TODO: custom pane saving... somewhere
		
		return array(
			'left' => array(
				array(
					'title' => __('Nodes'),
					'href' => $this->view->url('admin/pane/nodes')
				),
				array(
					'title' => __('Revisions'),
					'href' => $this->view->url('admin/pane/revisions')
				),
				
			),
		);
	}
}
