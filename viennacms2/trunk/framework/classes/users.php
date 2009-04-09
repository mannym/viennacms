<?php
class Users {
	public $cookie;
	public $user;
	public $logged_in;
	public $session;
	public $session_storage = array();
	public $orig_storage;
	
	public function __construct() {
		@define('USER_OK', 0);
		@define('USER_NOT_FOUND', 1);
		@define('USER_WRONG_PASSWORD', 2);
	}
	
	public function exit_clean() {
		if ($this->user->user_id != 0) {
			$this->user->write(); // hey, who added this line of code?
		}
		
		$sid = $this->session->session_id;
		
		if ($sid && $this->session_storage != $this->orig_storage) {
			cms::$config['session_' . $sid] = serialize($this->session_storage);
		}
	}
	
	public function load_session_storage() {
		$sid = $this->session->session_id;
		
		if (isset(cms::$config['session_' . $sid])) {
			$this->session_storage = unserialize(cms::$config['session_' . $sid]);
			$this->orig_storage = $this->session_storage;
		}
	}
	
	public function cleanup() {
		$query = new VSession();
		$sessions = $query->read();
		
		if (empty(cms::$config['session_timeout'])) {
			cms::$config['session_timeout'] = (3600 * 24); // one day, like the cookie
		}
		
		foreach ($sessions as $session) {
			if ($session->session_time < (time() - cms::$config['session_timeout'])) {
				unset(cms::$config['session_' . $session->session_id]);
				$session->delete(false); // note the false... we don't want to lose the user!
			}
		}
	}
	
	public function initialize() {
		if (isset($_COOKIE['viennacms2_id'])) {
			$this->cookie = unserialize(stripslashes($_COOKIE['viennacms2_id']));
		} else {
			$this->cookie = array(
				'u' => false,
				's' => ''
			);
		}
		
		if ($this->cookie['u'] !== false && $this->cookie['s']) {
			$this->session = new VSession();
			$this->session->session_id = $this->cookie['s'];
			$this->session->read(true);
			
			if ($this->session->user_id == $this->cookie['u'] && $_SERVER['REMOTE_ADDR'] == $this->session->session_ip) {
				if ($this->session->user_id != 0) {
					$this->user = $this->session->user;
					$this->logged_in = true;
				} else {
					$this->user = $this->guest_profile();
					$this->logged_in = false;
				}
				
				$this->session->session_time = time();
				$this->session->write(false);
				
				$cookie = serialize($this->cookie);
				setcookie('viennacms2_id', $cookie, (time() + (3600 * 24)), '/', '');
				
				$this->load_session_storage();
				return;
			}
		}
		
		$this->logged_in = false;
		$this->create_session($this->guest_profile());
		$this->load_session_storage();
	}
	
	private function guest_profile() {
		$user = new VUser();
		$user->user_id = 0;
		$user->username = __('Anonymous');
		
		return $user;
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
		
		$this->logout(); // end the old session
		$this->create_session($user);

		$this->logged_in = true;
		
		return USER_OK;
	}
	
	public function create_session($user) {
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
	}
	
	public function logout() {
		$this->session->delete(false);
		setcookie('viennacms2_id', '', (time() - 3600), '/', '');
		$this->logged_in = false;
	}
}
