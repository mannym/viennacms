<?php
/**
* cms
* The main management/storage class, which keeps variables, and has utility functions.
* 
* @package viennaCMS2
* @version $Id$
* @access public
*/
class cms {
	static $db;
	static $vars;
	static $user;
	static $manager;
	static $router;
	static $layout;
	static $cache;
	
	/**
	* cms::register()
	* Registers one of the required objects. Your own objects should be placed in the $vars array.
	* 
	* @param string $name Object name, should be a property in this file
	* @param mixed $object Object to store.
	* @return void
	*/
	public static function register($name, $object) {
		if (empty(self::$$name)) {
			self::$$name = $object;
		}
	}
	
	/**
	* cms::display_allowed()
	* Utility function, asks all node hooks if a specific node may be located under another node.
	* 
	* @param string $type The type of check that needs to be done. Seemingly, only 'this_under_other' is implemented now.
	* @param Node $node The 'this' node to be checked.
	* @param mixed $other The 'other' node, may be false.
	* @return bool success value
	*/
	public static function display_allowed($type, $node, $other = false) {
		$results = manager::run_hook_all('display_allowed', $type, $node, $other);
		
		foreach ($results as $result) {
			if ($result == false) {
				return false;
			}
		}
		
		return true;
	}
	
	/**
	* cms::get_admin_tree()
	* Gets a tree structure for all nodes - for use in the ACP.
	* 
	* @todo make this a more generic function, which can also be used outside of the ACP.
	* @param string $node_link_template Template for the link target. May contain the following replacement tags: %node_id, %node_type
	* @return string tree HTML
	*/
	public static function get_admin_tree($node_link_template) {
		$node = new Node();
		$node->node_id = 0;
		return self::_get_admin_tree($node, '', $node_link_template);
	}
	
	/**
	* cms::_get_admin_tree()
	* internal function for get_admin_tree()
	*/
	private static function _get_admin_tree($node, $list = '', $nlt = '') {
		if ($node->node_id != 0) {
			$pnlt = str_replace(
				array('%node_id', '%node_type'),
				array($node->node_id, $node->type),
				$nlt
			);
			$list .= '<li id="node-' . $node->node_id . '"><a href="' . $pnlt . '" class="' . $node->type . '">' . $node->title . '</a>' . "\r\n";			
		}
			
		$nodes = $node->get_children();

		$my_id = $node->node_id;
			
		if ($nodes) {
			$list .= '<ul>';
			foreach ($nodes as $node) {
				$list = self::_get_admin_tree($node, $list, $nlt);
			}
			$list .= '</ul>';
		}
		
		$list .= '</li>';
		return $list;
	}
	
	/**
	* @todo pretty up the error page
	*/
	public static function handle_exception($exception) {
		$error_data = array(
			'code' => $exception->getCode(),
			'file' => $exception->getFile(),
			'line' => $exception->getLine()
		);
		
		$string = base64_encode(str_rot13(serialize($error_data)));
		$lines = implode("\n", str_split($string, 60));
		
		echo '<html><body>';
		echo '<h1>viennaCMS: critical error</h1>';
		echo $exception->getMessage();
		echo '<h2>Debug information (for developers)</h2><pre>';
		echo $lines;
		echo '</pre></body></html>';
	}
}
