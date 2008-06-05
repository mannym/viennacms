<?php
/**
* User class for viennaCMS.
* "Hey, what are you doing in the ACP? Have you logged in?"
* 
* @package user
* @author viennacms.nl
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
* @package user
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
	 * 
	 * @param bool $checklogin: Any need to check login?
	 */
	
	public function initialize($checklogin = false) {
		if($checklogin) {
			$login = $this->checkcookie();
			if(!$login) return false;
			$login = $this->login_correct($this->userid);
			$this->user_logged_in = $login;
			$this->getlanguage();			
		}
	}
	
	/**
	 * Check if the user is logged in. You can check it after calling this function, by 
	 * the variable user_logged_in 
	 * 
	 * @return bool $login: user logged in?
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
		// Standard true
		$login = true;
		$db = database::getnew();
		// Get all user data
		$sql = "SELECT * FROM " . USER_TABLE . " WHERE username = '" . $db->sql_escape($username) . "'"; 
		if(!$result = $db->sql_query($sql)) {
			$login = false;
		}
		$logindata = $db->sql_fetchrow($result);
		$max_attempts = false;
		if($logindata['login_attempts'] >= 3 && $logindata['last_login_attempt'] > (time() - 120))
		{
			$login = false;
			$max_attempts = true;
		}
		if(!$max_attempts && $logindata['password'] != md5($password)) {
			$login = false;
			if($logindata['login_attempts'] >= 3 && $logindata['last_login_attempt'] < (time() - 120))
			{
				$new_login_attempts = 1;
			}
			else {
				$new_login_attempts = $logindata['login_attempts'] + 1;
			}
			$logindata['login_attempts'] = $new_login_attempts;
			$sql = 'UPDATE ' . USER_TABLE . ' SET login_attempts = ' . $new_login_attempts . ',
					last_login_attempt = \'' . time() . '\' WHERE userid = ' . $logindata['userid'];
			$db->sql_query($sql);
		}
		$this->userid = $logindata['userid'];
		$this->md5_password		= md5(md5($password));
		$this->data = $logindata;
		if($login) {
			$login = $this->login_correct($logindata['userid'], true);
			// Reset login attempts
			$sql = 'UPDATE ' . USER_TABLE . '
					SET login_attempts = 0, last_login_attempt = 0
					WHERE userid = ' . $this->userid;
		}
		elseif($max_attempts)
		{
			trigger_error(__('Login limit reached. Please wait 2 minutes before attempting to login again.'));
			return false;
		}
		elseif(!$login && !$max_attempts) {
			trigger_error(__('Login incorrect'));		
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
		
		utils::run_hook_all('admin_init');
		
		return;
	}
	
	public function getlanguage() {
		global $db;
		$language	= $this->data['lang'];
		
		// Set language to $language
		_setlocale(LC_ALL, $language);
	
		// Specify location of translation tables
		_bindtextdomain("viennacms", ROOT_PATH . "locale");

		// Choose domain
		_textdomain("viennacms");
		// Translation is looking for in ./locale/$language/LC_MESSAGES/viennacms.mo now
		return true;
	}
}

?>
