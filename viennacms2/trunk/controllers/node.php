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
		echo 'Hi!';
	}
}
?>