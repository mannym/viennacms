<?php
/**
* User class for viennaCMS.
* "Hey, what are you doing in the ACP? Have you logged in?"
* 
* @package viennaCMS
* @author viennainfo.nl
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

/**
* @ignore
*/

if (!defined('IN_VIENNACMS')) {
	exit;
}

/**
* User class. Important stuff like login etc.
* 
* @package viennaCMS
* */

class user {
	static private $instance;
	
	public $username;
	public $line;
	public $data;
	public $userid;
	public $md5_password;
	public $randomstring;
	
	public $user_logged_in;
	
	/**
	* Return an instance of user.
	*/
	static function getnew() {
		if (!isset(self::$instance)) {
			self::$instance = new user;
			if (method_exists(self::$instance, 'initialize'))
			{
				self::$instance->initialize();
			}
		}
		return self::$instance;
	}
	
	/**
	 * Intialize the user class	
	 */
	
	public function initialize($checklogin = false) {
		if($checklogin) {
			$login = $this->checkcookie();
			if(!$login) return false;
			$login = $this->login_correct($this->userid);
			$this->user_logged_in = $login;
			$this->getlanguage();
			
			
		}
		
		// Do some stuff... :P		
	}
	
	/**
	 * Check if the user is logged in. You can check it after calling this function, by t
	 * the variable $this->user_logged_in
	 */
	
	private function checkcookie() {
		$login = true;
		// Check if cookies are set. If they aren, set the variables and set user_logged_in to true. If they aren't, just don't set the variables... 
		
		if(empty($_COOKIE['viennaCMS_userid']) || empty($_COOKIE['viennaCMS_randomstring']) || empty($_COOKIE['viennaCMS_md5passwd'])) {
			$login = false;
		}
		else {
			$this->userid 		= $_COOKIE['viennaCMS_userid'];
			$this->md5_password	= $_COOKIE['viennaCMS_md5passwd'];
			$this->randomstring = $_COOKIE['viennaCMS_randomstring'];
		}
		return $login;
	}
	
	/**
	 *  Check if the login is correct
	 */
	
	private function login_correct($userid, $in_login = false) {
		$login = true;
		$db = database::getnew();
		$sql = "SELECT * FROM " . USER_TABLE . " WHERE userid = " . $userid;
		$result = $db->sql_query($sql);
		$this->data = $db->sql_fetchrow($result);
		if($this->md5_password != md5($this->data['password'] . ($in_login ? '' : $this->randomstring ) )) {
			$login = false;
		}
		
		return $login;
	}
	
	
	/**
	 * Set the cookies
	 */
	private function set_login_cookies($logout = false) {
		$expire_val 	= ( ( $logout ) ? ( time() - 30 ) : time() + 1209600); // 1209600 is two weeks
		$randomstring	= uniqid(time());
		$md5_pass		= md5($this->data['password'] . $randomstring);
		$cookies = array(
			'viennaCMS_userid' 			=> $this->data['userid'],
			'viennaCMS_md5passwd'		=> $md5_pass,
			'viennaCMS_randomstring'	=> $randomstring,	
		);
		
		foreach($cookies as $cookie => $cookie_value) {
			setcookie($cookie, $cookie_value, $expire_val, '/', ''); 
		}
		return true;
	}
	

	
	/**
	 * Log in.
	 *
	 * @param string $username
	 * @param string $password
	 * @return bool succes
	 */
	public function login($username, $password) {
		$login = true;
		$db = database::getnew();
		// TODO: case insensitive usernames
		$sql = "SELECT * FROM " . USER_TABLE . " WHERE username = '" . $db->sql_escape($username) . "'"; 
		if(!$result = $db->sql_query($sql)) {
			$login = false;
		}
		$logindata = $db->sql_fetchrow($result);
		if($logindata['password'] != md5($password)) {
			$login = false;
		}
		$this->userid = $logindata['userid'];
		$this->md5_password		= md5(md5($password));
		$this->data = $logindata;
		if($login) {
			$login = $this->login_correct($logindata['userid'], true);
		}
		if(!$login) {
			trigger_error(__('Login incorrect'), E_USER_ERROR);
			
			return false;
		}
		$this->set_login_cookies();
		return true;
	}
	
	/**
	 * Logout
	 */
	
	public function logout() {
		$this->initialize(true);
		$this->set_login_cookies(true);
		unset($this->data);
	}
	
	
	/**
	 * Is the user authenticated to the ACP?
	 */
	public function checkacpauth() {
		$this->initialize(true);
		if (!$this->user_logged_in) {
			header('Location: ' . utils::base() . 'login.php');
			exit;
		}
		
		return;
	}
	
	public function getlanguage() {
		global $db;
		$sql = "SELECT lang FROM " . USER_TABLE . "
				WHERE userid = " . $this->userid;
		
		$result 	= $db->sql_query($sql);
		$row		= $db->sql_fetchrow($result);
		$language	= $row['lang'];
		
		// Set language to $language
		_setlocale(LC_ALL, $language);
	
		// Specify location of translation tables
		_bindtextdomain("viennacms", ROOT_PATH . "locale");

		// Choose domain
		_textdomain("viennacms");
		// Translation is looking for in ./locale/$language/LC_MESSAGES/viennaCMS.mo now
		return true;
	}
}

?>