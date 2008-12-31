<?php
class InstallController extends Controller {
	public function fresh() {
		if (isset(cms::$vars['sitenode']) && cms::$vars['sitenode']->title != 'viennaCMS installation') {
			trigger_error(__('viennaCMS is already installed!'));
		}
		
		$step = (!empty($this->arguments[0])) ? $this->arguments[0] : intval($_POST['step']);
		
		if (empty($step)) {
			$step = 1;
		}
		
		$this->view['step'] = $step;
		// no, this does not use the form API, that one is not suited for wizards
		
		switch ($step) {
			case 1:
				cms::$layout->view['title'] = __('Welcome');
				
				// TODO: do some requirement checks?
			break;
			case 2:
				// i'm getting bored... could you turn the volume down?
				cms::$layout->view['title'] = __('Database information');
				
				$this->view['dbhost'] = 'localhost';
				$this->view['table_prefix'] = 'viennacms_';
			break;
			case 3:
				$error = false;
				$dbhost = $_POST['dbhost'];
				$dbuser = $_POST['dbuser'];
				$dbpasswd = $_POST['dbpasswd'];
				$dbname = $_POST['dbname'];
				$table_prefix = $_POST['table_prefix'];
				
				include(ROOT_PATH . 'framework/database/mysqli.php');
				cms::$db = new database();
				cms::$db->return_on_error = true;
				$result = cms::$db->sql_connect($dbhost, $dbuser, $dbpasswd, $dbname);
			
				if (cms::$db->sql_error_triggered) {
					$error = $result;
				}
				
				if (!$error) {
					include(ROOT_PATH . 'framework/database/db_tools.php');
					include(ROOT_PATH . 'blueprint/schema.php');
				
					// we must do this so that we can handle the errors
					$db_tools = new cms_db_tools(cms::$db, true);
				
					foreach ($schema_data as $table_name => $table_data)
					{
						// Change prefix
						$table_name = preg_replace('#viennacms_#i', $table_prefix, $table_name);
				
						$statements = $db_tools->sql_create_table($table_name, $table_data);
				
						foreach ($statements as $sqlt)
						{
							if (!cms::$db->sql_query($sqlt))
							{
								$error = cms::$db->sql_error();
								break 2;
							}
						}
					}
	
				}
				
				// enable the blueprint
				spl_autoload_register(array('cms', 'autoload'));
				cms::$vars['table_prefix'] = $table_prefix;
				
				if (!$error) {
					// okay, let's initiate the models
					$node = Node::create('Node');
					$node->title = 'viennaCMS installation'; // don't translate this string!
					$node->description = __('A default viennaCMS web site');
					$node->type = 'site';
					$node->parent = 0;
					$node->created = time();
					$node->write();
					
					if (cms::$db->sql_error_triggered) {
						$error = cms::$db->sql_error();
					}	
					
					$parent = $node->node_id;
				}
				
				if (!$error) {
					// that worked, next please
					$node = Node::create('Node');
					$node->title = 'Home';
					$node->description = __('The default home page');
					$node->type = 'page';
					$node->parent = $parent;
					$node->revision->content = __('viennaCMS2 has been successfully installed. Go to your ACP, and add some pages! :-D');
					$node->write();
					
					if (cms::$db->sql_error_triggered) {
						$error = cms::$db->sql_error();
					}
					
					$home = $node->node_id;
				}
				
				if (!$error) {
					// set as homepage (and test reading)
					$node = new Node();
					$node->node_id = $parent;
					$node->read(true);
					
					if (empty($node->title)) {
						$error = array(
							'message' => __('Node system self-test failed. Now stop squirming and error out!')
						);
					}
				}
				
				if (!$error) {
					// come on, make it work!
					$node->options['homepage'] = $home;
					$node->write();
				}
				
				cms::$db->return_on_error = false;
				
				if (!$error) {
					$config = <<<CONFIG
<?php
\$dbms = 'mysqli';
\$dbhost = '$dbhost';
\$dbuser = '$dbuser';
\$dbpasswd = '$dbpasswd';
\$dbname = '$dbname';
\$table_prefix = '$table_prefix';

//define('DEBUG', true);
//define('DEBUG_EXTRA', true);
CONFIG;
// for buggy syntax highlighters: <?php
					$result = @file_put_contents(ROOT_PATH . 'config.php', $config);
					
					if (!$result) {
						$error = array(
							'message' => __('Could not write the configuration file.')
						);
					}
				}
				
				if ($error) {
					cms::$layout->view['title'] = __('Database information');
					
					$this->view['step'] = 2;
					$this->view['error'] = $error['message'];
					
					$this->view['dbhost'] = $dbhost;
					$this->view['dbuser'] = $dbuser;
					$this->view['dbname'] = $dbname;
					$this->view['table_prefix'] = $table_prefix;
					
					break;
				}
				
				// okay, let them enter the user information
				// due to system strangeness, the system should be runnable by now!
				cms::$layout->view['title'] = __('User information');
			break;
			case 4:
				// if everything's correct, we should be in the installed system by now
				// for usability, we should finish everything here... or blame the user if this goes wrong :D
				// TODO: link through to a 'initial configuration' wizard
				
				$error = false;
				$username = $_POST['username'];
				$password = $_POST['password'];
				$password2 = $_POST['password2'];
				
				if (empty($username)) {
					$error = __('You need to enter a user name.');
				}
				
				if (empty($password)) {
					$error = __('You need to enter a password.');
				}
				
				if ($password != $password2) {
					$error = __('The passwords do not match.');
				}
				
				if ($error) {
					cms::$layout->view['title'] = __('User information');
					
					$this->view['step'] = 3;
					$this->view['error'] = $error;
					
					$this->view['username'] = $username;
					
					break;
				}
				
				$user = User::create('User');
				$user->username = $username;
				$user->user_password = md5($password);
				$user->user_active = 1;
				$user->write();
				
				$object = Permission_Object::create('Permission_Object');
				$object->resource = 'admin:see_acp';
				$object->owner_id = $user->user_id;
				$object->permission_mask = 'y--';
				$object->write();
				
				cms::$user->login($username, $password);
				
				$node = new Node();
				$node->node_id = 1; // we hope :D
				$node->read(true);
				$node->title = 'viennaCMS';
				$node->write();
				
				header('Location: ' . manager::base());
				exit;
			break;
		}
		
		$this->view['action'] = $this->view->url('install/fresh');
	}
}
