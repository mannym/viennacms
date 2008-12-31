<?php
class AdminRevisionController extends Controller {
	public function view() {
		$node = new Node();
		$node->node_id = $this->arguments[0];
		$node->read(true);

		AdminController::add_pane('left', 'meta', array($node->node_id));
		
		$node->revision = new Node_Revision();
		$node->revision->node = $node->node_id;
		$node->revision->number = $this->arguments[1];
		$node->revision->read(true);
		
		// load the NodeController
		cms::$manager->get_controller('node');
		
		$this->view['node'] = $node;
		$this->view['revision_content'] = NodeController::node($node->node_id, $node->revision->number);
		$this->view['revision_date'] = date('d-m-Y G:i', $node->revision->time);
	}
}