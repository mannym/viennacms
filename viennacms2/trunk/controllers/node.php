<?php
class NodeController extends Controller {
	public $modules;

	public function show() {
		$node = new Node();
		$node->load('id = ?', array(intval($this->arguments[0])));
		
		if (!$node->id) {
			$this->global['manager']->page_not_found();
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
		$this->global['node'] = $node;
		$this->global['layout']->view['title'] = $node->title;
	}
	
	public function get_modules($location) {
		$content = '';
	
		foreach ($this->modules[$location] as $module) {
			$controller = $this->global['manager']->get_controller($module['controller']);
			$controller->view = new View($this->global);
			$controller->view->path = $module['controller'] . '.php';
			$controller->arguments = $module['arguments'];
			$controller->run();
			$content .= $controller->view->display();
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

		$this->global['router']->parts['action'] = 'show';
		$this->view->reset_path();
		$this->arguments = array((string) $this->global['sitenode']->options['homepage']);
		$this->show();
	}
}