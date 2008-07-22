<?php
require_once("PHPUnit/Framework/TestCase.php");

class TestCase_nodes extends PHPUnit_Framework_TestCase {
    protected function setUp() {
		$this->startSystem();
    }

    protected function tearDown() {

    }
    
    public function testCreateNode() {
    	$node = new CMS_Node();
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
        define('IN_VIENNACMS', true);
		require_once("./start.php");
//		$this->assertNotNull($db, 'database class not created');
		//$this->assertType('array', $config, '$config is not an array');
    }
}
?>
