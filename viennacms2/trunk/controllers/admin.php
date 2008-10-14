<?php
class AdminController extends Controller {
	public function main() {
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
		
		return true;
	}
}
