<?php
class UserController extends Controller {
	public function login() {
		if (isset($_POST['submit'])) {
			$result = $this->global['user']->login($_POST['username'], $_POST['password']);
			if ($result != USER_OK) {
				trigger_error(__('The entered username and/or password are incorrect.'));
			} else {
				header('Location: ' . $this->view->url('node'));
				exit;
			}
		}
	}
	
	public function logout() {
		if ($this->arguments[0] == $this->global['user']->session->session_id) {
			$this->global['user']->logout();
		}
		
		header('Location: ' . $this->view->url('node'));
		exit;
	}
}