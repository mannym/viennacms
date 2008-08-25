<?php
class NodeController extends Controller {
	public function show() {
		$node = new Node();
		$node->load('id = ?', intval($this->arguments[0]));
	
		$this->view['node'] = $node;
		$this->global['layout']->view['title'] = $node->title;
	}
	
	public function main() {
		/*$node = new Node();
		$node->title = 'Woef!';
		$node->parent = 2;
		$node->description = 'De blafnode :D';
		$node->type = 'page';
		$node->revision->content = 'Woef :D';
		$node->save();
		
		$node = new Node();
		$node->load('id = 7');
		echo $node->revision->content;
			
		$node = new Node();
		$node->load('id = 7');
		$node->revision->content = 'Woef x2';
		$node->save();
		*/

		$this->global['router']->parts['action'] = 'show';
		$this->arguments = array(1);
		$this->show();
	}
}
?>