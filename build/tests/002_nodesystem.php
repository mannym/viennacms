<?php
require_once("PHPUnit/Framework/TestCase.php");

class TestCase_002_nodes extends PHPUnit_Framework_TestCase {
    protected function setUp() {
		$this->startSystem();
    }

    protected function tearDown() {

    }
    
    public function testCreateNode() {
    	$node = CMS_Node::getnew();
    	$node->parent_id = 1;
    	$node->title = 'First node';
    	$node->description = '';
    	$node->title_clean = 'first-node';
    	$node->type = 'page';
    	$node->write();
   	$this->assertEquals(2, $node->node_id, 'Node ID is not 2');
    }

    public function startSystem() {
    	global $cache, $db, $template; // for not die-ing with a fatal error
        global $dbhost, $dbuser, $dbpasswd, $dbname, $table_prefix;
	if (!file_exists('config.php')) {
		file_put_contents('config.php',
<<<CONFIG
<?php
\$dbhost = 'localhost';
\$dbuser = 'root';
\$dbpasswd = '';
\$dbname = 'viennacms_unittest';
\$dbms = 'mysql';

\$table_prefix = 'viennacms_';

@define('CMS_INSTALLED', true);
?>
CONFIG
);
	}
	        define('IN_VIENNACMS', true);
		require_once("./start.php");
//		$this->assertNotNull($db, 'database class not created');
		//$this->assertType('array', $config, '$config is not an array');
    }
}
?>
