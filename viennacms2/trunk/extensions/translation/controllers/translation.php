<?php
class TranslationController extends Controller {
	public function select() {
		setcookie('viennacms2_tset', $this->arguments[0], (time() + (3600 * 24 * 30)), '/', '');
		
		$target = base64_decode($_GET['goto']);
		
		if ($target == '') {
			$target = 'node';
		}

		cms::redirect($target);
	}
}