<?php
class NodeController {
	public function show() {
		echo 'Showing node ' . $this->arguments[0];
	}
	
	public function delete() {
		echo 'Not allowed';
	}
	
	public function main() {
		echo 'Hi!';
	}
}
?>