<?php
class HTMLContentController extends Controller {
	public function run() {
		$this->view['content'] = $this->arguments['content']; 
	}
}
?>