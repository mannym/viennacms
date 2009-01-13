<?php
class AdminFileController extends Controller {
	public function folder() {
		$this->view->path = 'admin/simple.php';
		
		$this->view['data'] = '';
	}
}
