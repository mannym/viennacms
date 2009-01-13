<?php
class Files {
	public $fileroot;
	
	public function init() {
		$node = new Node();
		$node->parent = 0;
		$node->type = 'filesfolder';
		$node->read(true);
		
		if (empty($node->title)) {
			$node = Node::create('Node');
			$node->parent = 0;
			$node->type = 'filesfolder';
			$node->title = 'Files.localized';
			$node->write();
		}

		$this->fileroot = $node;
	}
}
