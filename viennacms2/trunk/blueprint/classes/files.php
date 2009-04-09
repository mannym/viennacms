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
			__('Files'); // dummy for poedit
			$node->title = 'Files.localized';
			$node->write();
		}

		$this->fileroot = $node;
	}
	
	public function get_file_widget($file) {
		$output = new stdClass;
		$output->output = '';
		$output->append = '';
		
		manager::run_hook_all('core_file_widget', $file, $output);
		
		if ($output->output == '') {
			manager::run_hook_all('core_file_default_widget', $file, $output);
		}
		
		return $output->output . $output->append;
	}
}
