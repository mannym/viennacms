<?php
class Helpers {
	public $trashroot;
	
	public function init_trash() {
		$node = new Node();
		$node->parent = 0;
		$node->type = 'trashcan';
		$node->cache = 7200;
		$node->read(true);
		
		if (empty($node->title)) {
			$node = Node::create('Node');
			$node->parent = 0;
			$node->type = 'trashcan';
			__('Trash'); // dummy
			$node->title = 'Trash.localized';
			$node->write();
		}

		$this->trashroot = $node;
	}
	
	public function remove_node_children($node) {
		$children = $node->get_children();
		
		foreach ($children as $child) {
			$this->remove_node($child);
		}
	}
	
	public function remove_node($node) {
		$this->remove_node_children($node);
		$node->delete();
	}
	
	public function render_modules($source_modules) {
		if (empty($source_modules)) {
			return ''; // don't waste our time on this :)
		}

		$content = '';

		$modules = array();
		
		foreach ($source_modules as $id => $module) {
			$modules[$module['order']] = $module;
		}

		ksort($modules);
		
		foreach ($modules as $module) {
			$box = new View();
			$box->path = 'style/box.php';
			$controller = cms::$manager->get_controller($module['controller']);
			
			if (!$controller) {
				continue;
			}
			
			$controller->view = new View();
			$controller->view->path = $module['controller'] . '.php';
			$controller->arguments = $module['arguments'];
			$return = $controller->run();
			$box['controller'] = $module['controller'];
			$box['title'] = $return['title'];
			$box['content'] = $return['content'];
			$content .= $box->display();
		}
		
		return $content;
	}
		
	/**
	* cms::get_admin_tree()
	* Gets a tree structure for all nodes - for use in the ACP.
	* 
	* @deprecated replaced by get_tree()
	* @param string $node_link_template Template for the link target. May contain the following replacement tags: %node_id, %node_type
	* @return string tree HTML
	*/
	public function get_admin_tree($node_link_template, $selected = false) {
		$options = array(
			'url' => $node_link_template,
			'url_from' => 'admin_tree',
			'selected' => $selected
		);
		
		return $this->get_tree($options);
	}
	
	/**
	* cms::get_admin_tree()
	* Gets a tree structure for all nodes - for use in the ACP.
	* 
	* @todo update documentation :)
	* @param string $node_link_template Template for the link target. May contain the following replacement tags: %node_id, %node_type
	* @return string tree HTML
	*/
	public function get_tree($options = array()) {
		if (!isset($options['node'])) {
			$node = new Node();
			$node->node_id = 0;
		} else {
			$node = $options['node'];
		}
		
		return $this->_get_tree($node, '', $options);
	}
	
	/**
	* cms::_get_admin_tree()
	* internal function for get_admin_tree()
	*/
	private function _get_tree($node, $list, $options) {
		if ($node->node_id != 0) {
			$selected = $options['selected'];
			
			if ($options['url']) {
				if (empty($options['url_from'])) {
					$options['url_from'] = 'none';
				}
				
				if (is_string($options['url'])) {
					$nlt = $options['url'];
					
					$pnlt = str_replace(
						array('%node_id', '%node_type'),
						array($node->node_id, $node->type),
						$nlt
					);
					
					$url = new stdClass;
					$url->url = $pnlt;
					
					manager::run_hook_all('core_get_admin_tree', $options['url_from'], $url, $nlt, $node);
				} else if (is_callable($options['url'])) {
					$url = new stdClass;
					$url->url = call_user_func_array($options['url'], array($node));
				}
				
				$pua = '';
				
				if (is_string($options['url_attributes'])) {
					$ua = $options['url_attributes'];
					
					$pua = str_replace(
						array('%node_id', '%node_type'),
						array($node->node_id, $node->type),
						$ua
					);
				}
				
				$class = '';
				
				if (is_numeric($selected) && $node->node_id == $selected) {
					$class = ' selected';
				}
				
				$list .= '<li id="node-' . $node->node_id . '"><a href="' . View::url($url->url) . '" class="' . $node->type . $class . '"' . $pua . '>' . $node->title . '</a>' . "\n";
			} else {
				$class = '';
				
				if (is_numeric($selected) && $node->node_id == $selected) {
					$class = ' selected';
				}
				
				$list .= '<li id="node-' . $node->node_id . '" class="' . $node->type . $class . '">' . $node->title . "\n";			
			}
		}
			
		$nodes = $node->get_children();

		$my_id = $node->node_id;
			
		if ($nodes) {
			$list .= '<ul>';
			foreach ($nodes as $node) {
				$show = true;
				
				if ($options['display_callback']) {
					if (is_callable($options['display_callback'])) {
						$show = call_user_func_array($options['display_callback'], array($node));
					} else if (is_string($options['display_callback'])) {
						$results = manager::run_hook_all($options['display_callback'], $node);
		
						foreach ($results as $result) {
							if ($result == false) {
								$show = false;
							}
						}
					}
				}
					
				if ($show) {
					$list = $this->_get_tree($node, $list, $options);
				} else {
					$list = $list;
				}
			}
			$list .= '</ul>';
		}
		
		$list .= '</li>';
		return $list;
	}
	
	public function return_bytes($val) {
	    $val = trim($val);
    	$last = strtolower($val[strlen($val)-1]);
	    switch($last) {
    	    // The 'G' modifier is available since PHP 5.1.0
	        case 'g':
        	    $val *= 1024;
    	    case 'm':
	            $val *= 1024;
        	case 'k':
    	        $val *= 1024;
	    }

    	return $val;
	}
	
	public function validate_node($id) {
		$node = new Node();
		$node->node_id = $id;
		$node->read(true);
		
		if (!$node->title) {
			return __('The node does not exist.');
		}
	}
	
	public function create_node_alias($node) {
		if ($node->typedata['display_callback'] != 'none') {
			$path = $this->create_node_parent_path($node);
			
			$path .= cms::$router->clean_title($node->title);
			$path .= '.html';
			
			$hpath = new stdClass;
			$hpath->path = $path;
			
			if ($node->typedata['path_callback']) {
				call_user_func_array($node->typedata['path_callback'], array($node, $hpath));
			}
			
			cms::$router->add_url_alias($hpath->path, 'node/show/' . $node->node_id);
		}
	}
	
	public function create_node_parent_path($node) {
		$path = '';
		
		if ($node->parent) {
			$parents = cms::$layout->get_parents($node->get_parent());
			array_shift($parents);
			foreach ($parents as $parent) {
				$path .= cms::$router->clean_title($parent->title) . '/';
			}
		}
		
		return $path;
	}
	
	public function readable_size($bytes) {
        $b = (int) $bytes;
        $s = array('B', 'kB', 'MB', 'GB', 'TB');
        if ($b < 0) {
            return '0 ' . $s[0];
        }
        $con = 1024;
        $e = (int) (log($b, $con));
        return number_format($b / pow($con, $e), 2, ',', '.') . ' ' . $s[$e]; 
	}
}
