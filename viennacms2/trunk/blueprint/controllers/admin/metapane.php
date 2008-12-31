<?php
class AdminMetaPaneController extends Controller {
	function main() {
		$node = new Node();
		$node->node_id = $this->arguments[0];
		$node->read(true);
		
		if (empty($node->title)) {
			$this->view['error'] = __('Please select a node before showing meta data.');
			return;
		}
		
		$meta = manager::run_hook_all('acp_metadata', $node, $this);
		
		foreach ($meta as $key => $data) {
			$output = '<li><a class="' . $key . '" href="#">' . $data['title'] . '</a><ul>';
			$output .= $data['content'];			
			$output .= '</ul></li>';
		}
		
		$this->view['output'] = $output;
	}
}