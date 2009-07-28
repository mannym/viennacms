<?php
class NodeController extends Controller {
	public $modules;

	public function show($id = false, $revision = false) {
		$node = new Node();
		if (!$id) {
			$node->node_id = $this->arguments[0];
		} else {
			$node->node_id = $id;
		}
		
		$node->open_readonly = true;
		$node = $node->read();
		$node = $node[0];
		
		if ($node->typedata['display_callback'] == 'none') {
			return cms::$manager->page_not_found();
		}
		
		if ($revision !== false) {
			$node->revision = new Node_Revision();
			$node->revision->node = $node->node_id;
			$node->revision->number = $revision;
			$node->revision->read(true);
		}
		
		if (!$id) {
			cms::$vars['node'] = $node;
		}
		
		if (!$node->title && !$id) {
			return cms::$manager->page_not_found();
		}
		
		VEvents::invoke('node.pre-show', $node);
		
		if (!$node->is_legacy) {
			array_shift($this->arguments);
			
			$result = $node->display($this->arguments);
			
			if (is_a($result, 'View')) {
				$this->view['content'] = $result->display();
			} else if ($result == CONTROLLER_NO_LAYOUT) {
				return $result;
			} else if (is_string($result)) {
				$this->view['content'] = $result;
			}
		} else {
			if ($node->typedata['type'] == 'static') {
				$this->view['type'] = 'static';
			} else if ($node->typedata['type'] == 'dynamic') {
				$this->modules = unserialize($node->revision->content);
				
				$this->view['content'] = $this->get_modules('content');
				// TODO: make left and such
			}
			
			// TODO: shift $arguments
			
			if ($node->typedata['display_callback']) {
				$result = call_user_func_array($node->typedata['display_callback'], array($node, $this->arguments));
				
				if (!$result) {
					return CONTROLLER_NO_LAYOUT;
				}
				
				$this->view['content'] = $result;
			}
		}
		
		$this->view['node'] = $node;	

		if (!$id) {
			cms::$layout->set_title($node->title);
		}
	}
	
	/**
	 * Display an embedded node.
	 *
	 * @param int $id Node ID
	 * @param mixed $revision Revision number to retrieve
	 * @return string node output
	 * @example
	 * <code>
	 * NodeController::node(7);
	 * </code>
	 */
	static function node($id, $revision = false) {
		$controller = new NodeController();
		$controller->view = new View();
		$controller->view->path = 'node/show.php';
		$controller->show($id, $revision);
		return $controller->view->display();
	}
	
	public function get_modules($location) {
		if (empty($this->modules[$location])) {
			return __('This node doesn\'t have any modules.');
		}
		
		return cms::$helpers->render_modules($this->modules[$location]);
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