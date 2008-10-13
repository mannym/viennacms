<?php
class MiniNodeController extends Controller {
	public function run() {
		return array(
			'title' => $this->arguments['title'],
			'content' => NodeController::node(intval($this->arguments['node']))
		);
	}
}
?>