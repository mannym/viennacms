<?php
class NodeController {
	public function show() {
		$this->view['node_id'] = $this->arguments[0];
		$this->view->display();
	}
	
	public function delete() {
		echo 'Not allowed';
	}
	
	public function main() {
		$node = new Node();
		$node->load('id = 2');
		var_dump($node->title);
		$parent = $node->get_parent();
		var_dump($parent->title);
	}
}
?>