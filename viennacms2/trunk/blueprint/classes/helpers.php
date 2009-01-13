<?php
class Helpers {
		
	/**
	* cms::get_admin_tree()
	* Gets a tree structure for all nodes - for use in the ACP.
	* 
	* @todo make this a more generic function, which can also be used outside of the ACP.
	* @param string $node_link_template Template for the link target. May contain the following replacement tags: %node_id, %node_type
	* @return string tree HTML
	*/
	public function get_admin_tree($node_link_template, $selected = false) {
		$node = new Node();
		$node->node_id = 0;
		return $this->_get_admin_tree($node, '', $node_link_template, $selected);
	}
	
	/**
	* cms::_get_admin_tree()
	* internal function for get_admin_tree()
	*/
	private function _get_admin_tree($node, $list = '', $nlt = '', $selected = false) {
		if ($node->node_id != 0) {
			$pnlt = str_replace(
				array('%node_id', '%node_type'),
				array($node->node_id, $node->type),
				$nlt
			);
			
			$url = new stdClass;
			$url->url = $pnlt;
			
			manager::run_hook_all('core_get_admin_tree', 'url', $url, $nlt, $node);
			
			$class = '';
			
			if (is_numeric($selected) && $node->node_id == $selected) {
				$class = ' selected';
			}
			
			$list .= '<li id="node-' . $node->node_id . '"><a href="' . View::url($url->url) . '" class="' . $node->type . $class . '">' . $node->title . '</a>' . "\r\n";			
		}
			
		$nodes = $node->get_children();

		$my_id = $node->node_id;
			
		if ($nodes) {
			$list .= '<ul>';
			foreach ($nodes as $node) {
				$list = $this->_get_admin_tree($node, $list, $nlt, $selected);
			}
			$list .= '</ul>';
		}
		
		$list .= '</li>';
		return $list;
	}
}
