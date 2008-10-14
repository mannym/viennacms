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
		
		return true;
	}
	
	public function panes() {
		$this->check_auth();
		echo json_encode($this->get_panes());
		exit;
	}
	
	public function pane() {
		echo $this->arguments[0];
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
