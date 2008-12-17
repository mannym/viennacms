<?php
class UserController extends Controller {
	public function login() {
		$form_data = array(
			'fields' => array(
				'username' => array(
					'label' => __('Username'),
					'description' => __('The username that belongs to your user account.'),
					'required' => true,
					'type' => 'textbox',
					'group' => 'login',
					'weight' => 0
				),
				'password' => array(
					'label' => __('Password'),
					'description' => __('The (case sensitive) password for your user account.'),
					'required' => true,
					'type' => 'password',
					'group' => 'login',
					'weight' => 0
				)
			),
			'groups' => array(
				'login' => array(
					'title' => __('User login'),
					'expanded' => true
				)
			)
		);
		
		$form = new Form();
		$form->callback_object = $this;
		$this->view['form_output'] = $form->handle_form('user_login', $form_data);
		
		cms::$layout->set_title(__('User login'));
	}
	
	public function user_login_validate($fields, &$errors) {
		$result = cms::$user->login($fields['username'], $fields['password']);
		if ($result == USER_NOT_FOUND) {
			$errors['username'] = __('This username does not exist.');
		} else if ($result == USER_WRONG_PASSWORD) {
			$errors['password'] = __('The entered password is incorrect.');
		}
	}
	
	public function user_login_submit() {
		header('Location: ' . $this->view->url('node'));
		exit;
	}
	
	public function logout() {
		if ($this->arguments[0] == cms::$user->session->session_id) {
			cms::$user->logout();
		}
		
		header('Location: ' . $this->view->url('node'));
		exit;
	}
}