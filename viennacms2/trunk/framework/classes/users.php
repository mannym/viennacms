<?php
class Users {
	public $cookie;
	public $user;
	public $logged_in;
	public $session;
	
	public function __construct() {
		@define('USER_OK', 0);
		@define('USER_NOT_FOUND', 1);
		@define('USER_WRONG_PASSWORD', 2);
	}
	
	public function __destruct() {
		$this->user->write(); // hey, who added this line of code?
	}
	
	public function initialize() {
		if (isset($_COOKIE['viennacms2_id'])) {
			$this->cookie = unserialize(stripslashes($_COOKIE['viennacms2_id']));
		} else {
			$this->cookie = array(
				'u' => 0,
				's' => ''
			);
		}
		
		if ($this->cookie['u'] && $this->cookie['s']) {
			$this->session = new VSession();
			$this->session->session_id = $this->cookie['s'];
			$this->session->read(true);
			
			if ($this->session->user_id == $this->cookie['u'] && $_SERVER['REMOTE_ADDR'] == $this->session->session_ip) {
				$this->user = $this->session->user;
				$this->logged_in = true;
				$this->session->session_time = time();
				$this->session->write(false);
				return;
			}
		}
		
		$this->logged_in = false;
		$this->user = new VUser();
		$this->user->user_id = 0;
		$this->user->username = 'Anonymous';
	}
	
	public function login($username, $password) {
		$user = new VUser();
		$user->username = $username;
		$user->read(true);
		
		if (!$user->user_id) {
			return USER_NOT_FOUND;
		}
		
		if ($user->user_password != md5($password)) {
			return USER_WRONG_PASSWORD;
		}
		
		$sid = md5(uniqid(time()));
		
		$this->session = new VSession();
		$this->session->user_id = $user->user_id;
		$this->session->session_id = $sid;
		$this->session->session_time = time();
		$this->session->session_ip = $_SERVER['REMOTE_ADDR'];
		$this->session->write();
		$this->user = $user;
		
		$this->cookie = array(
			'u' => $user->user_id,
			's' => $sid
		);
		
		$cookie = serialize($this->cookie);
		setcookie('viennacms2_id', $cookie, (time() + (3600 * 24)), '/', '');
		$this->logged_in = true;
		
		return USER_OK;
	}
	
	public function logout() {
		$this->session->delete(false);
		setcookie('viennacms2_id', '', (time() - 3600), '/', '');
		$this->logged_in = false;
	}
}
