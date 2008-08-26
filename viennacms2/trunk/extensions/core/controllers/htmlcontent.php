<?php
class HTMLContentController extends Controller {
	public function run() {
		return array(
			'title' => $this->arguments['title'],
			'content' => $this->arguments['content']
		);
	}
}
?>