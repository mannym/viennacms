<?php
class cms {
	static $db;
	static $vars;
	static $user;
	static $manager;
	static $router;
	static $layout;
	
	public static function register($name, $object) {
		self::$$name = $object;
	}
	
	public static function display_allowed($type, $node, $other) {
		$results = manager::run_hook_all('display_allowed', $type, $node, $other);
		
		foreach ($results as $result) {
			if ($result == false) {
				return false;
			}
		}
		
		return true;
	}
	
	public static function get_admin_tree($node_link_template) {
		$node = new Node();
		$node->node_id = 0;
		return self::_get_admin_tree($node, '', $node_link_template);
	}
	
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
}
