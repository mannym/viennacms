<?php
require_once("PHPUnit/Framework/TestCase.php");

class TestCase_start extends PHPUnit_Framework_TestCase {
    protected function setUp() {

    }

    protected function tearDown() {

    }

    public function testStartSystem() {
    	global $cache, $db, $template; // for not die-ing with a fatal error
        define('IN_VIENNACMS', true);
		define('IN_INSTALL', true);
		require_once("./start.php");
//		$this->assertNotNull($db, 'database class not created');
		$this->assertNotNull($template, 'template class not created');
		$this->assertNotNull($cache, 'acm class not created');
		$this->assertEquals(true, class_exists('CMS_Node'), 'CMS_Node does not exist');
		$this->assertEquals(true, class_exists('page'), 'page does not exist');
		$this->assertEquals(true, class_exists('user'), 'user does not exist');
		//$this->assertType('array', $config, '$config is not an array');
    }

/*       public function testInstall() {
                include(ROOT_PATH . 'includes/db/mysql.php');
                $db = database::getnew();
                $db->sql_connect('localhost', 'root', '', 'viennacms_unittest');
                $db->prefix = 'viennacms_';
                $this->assertNotEquals(false, $db->db_connect_id, 'could not connect to DB - sure details are correct?');
                include(ROOT_PATH . 'includes/functions_install.php');
                $this->assertEquals(true, function_exists('install_database'), 'function for installing not correct?');
                $dbresult = install_database('viennacms_', 'admin', md5('admin'));
                $this->assertEquals(false, $dbresult, 'database not created correctly, returned: ' . $dbresult);
        }
*/
	public function testConfigFile() {
		$correct = <<<CONFIG
<?php
\$dbhost = 'localhost';
\$dbuser = 'root';
\$dbpasswd = '';
\$dbname = 'viennacms_unittest';
\$dbms = 'mysql';

\$table_prefix = 'viennacms_';

@define('CMS_INSTALLED', true);
?>
CONFIG;
		$correct = str_replace("\r\n", "\n", $correct);
		$this->assertEquals(strlen($correct), utils::config_file_write('localhost', 'root', '', 'viennacms_unittest', 'viennacms_', 'mysql'), 'config file writing failed');
		$this->assertFileExists(ROOT_PATH . 'config.php', 'config file does not exist');
		$this->assertEquals(file_get_contents(ROOT_PATH . 'config.php'), $correct, 'config file does not contain correct data');
	}
	
	public function testInstall() {
		include(ROOT_PATH . 'includes/db/mysql.php');
		$db = database::getnew();
		$db->sql_connect('localhost', 'root', '', 'viennacms_unittest');
		$db->prefix = 'viennacms_';
		$this->assertNotEquals(false, $db->db_connect_id, 'could not connect to DB - sure details are correct?');
		include(ROOT_PATH . 'includes/functions_install.php');
		$this->assertEquals(true, function_exists('install_database'), 'function for installing not correct?');
		$dbresult = install_database('viennacms_', 'admin', md5('admin'), 'mysql');
		$this->assertEquals(false, $dbresult, 'database not created correctly, returned: ' . $dbresult);
	}
}
?>
