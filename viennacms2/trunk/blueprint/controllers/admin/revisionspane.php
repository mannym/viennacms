<?php
class AdminRevisionsPaneController extends Controller {
	function main() {
		$node = new Node();
		$node->node_id = $this->arguments[0];
		$node->read(true);
		
		if (empty($node->title)) {
			$this->view['error'] = __('Please select a node before showing revisions.');
			return;
		}
		
		$revisions = new Node_Revision();
		$revisions->node = $node->node_id;
		$revisions->order = array('time' => 'desc');
		$revisions = $revisions->read();
		$output = '';
		
		foreach ($revisions as $revision) {
			$output .= '<li><a class="page" href="' . $this->view->url('admin/controller/revision/view/' . $node->node_id . '/' . $revision->number) . '">' . sprintf(__('Revision %d'), $revision->number) . '</a></li>';
		}
		
		$this->view['output'] = $output;
	}
}