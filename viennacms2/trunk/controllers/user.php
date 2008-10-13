<?php
class UserController extends Controller {
	public function login() {
		if (isset($_POST['submit'])) {
			$result = cms::$user->login($_POST['username'], $_POST['password']);
			if ($result != USER_OK) {
				trigger_error(__('The entered username and/or password are incorrect.'));
			} else {
				header('Location: ' . $this->view->url('node'));
				exit;
			}
		}
	}
	
	public function logout() {
		if ($this->arguments[0] == cms::$user->session->session_id) {
			cms::$user->logout();
		}
		
		header('Location: ' . $this->view->url('node'));
		exit;
	}
}