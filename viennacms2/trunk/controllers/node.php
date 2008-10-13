<?php
class NodeController extends Controller {
	public $modules;

	public function show($id = false) {
		$node = new Node();
		if (!$id) {
			$node->node_id = $this->arguments[0];
		} else {
			$node->node_id = $id;
		}
		
		$node->read(true);
		
		if (!$node->node_id && !$id) {
			cms::$manager->page_not_found();
		}
		
		$types = manager::run_hook_all('get_node_types');
		if ($types[$node->type]['type'] == 'static') {
			$this->view['type'] = 'static';
		} else {
			$this->modules = unserialize($node->revision->content);
			
			$this->view['content'] = $this->get_modules('content');
			// TODO: make left and such
		}

		$this->view['node'] = $node;	

		if (!$id) {
			cms::$vars['node'] = $node;
			cms::$layout->view['title'] = $node->title;
		}
	}
	
	/**
	 * Display an embedded node.
	 *
	 * @param int $id Node ID
	 * @param GlobalStore $global the global storage
	 * @return string node output
	 * @example
	 * <code>
	 * NodeController::node(7, $this->global);
	 * </code>
	 */
	static function node($id) {
		$controller = new NodeController();
		$controller->view = new View();
		$controller->show($id);
		return $controller->view->display();
	}
	
	public function get_modules($location) {
		$content = '';
	
		foreach ($this->modules[$location] as $module) {
			$box = new View($this->global);
			$box->path = 'style/box.php';
			$controller = cms::$manager->get_controller($module['controller']);
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
	
	public function main() {
		/*
		$node = new Node();
		$node->title = 'Woof4';
		$node->parent = 1;
		$node->description = 'second test node';
		$node->type = 'dynpage';
		$node->revision->content = serialize(array(
			'content' => array(
				'controller' => 'htmlcontent',
				'arguments' => array(
					'content' => '<strong>Hellos</strong>! :D'
				)
			)
		));
		$node->save();

		$node = new Node();
		$node->load('id = 7');
		echo $node->revision->content;
		*/
	
		/*
		$node = new Node();
		$node->load('id = 8');
		$node->revision->content = serialize(array(
			'content' => array(
				array(
					'controller' => 'htmlcontent',
					'arguments' => array(
						'content' => '<strong>Hellos</strong>! :D'
					)
				)
			)
		));
		$node->save();
		*/

		cms::$router->parts['action'] = 'show';
		$this->view->reset_path();
		$this->arguments = array((string) cms::$vars['sitenode']->options['homepage']);
		$this->show();
	}
}