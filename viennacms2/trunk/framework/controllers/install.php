<?php
class InstallController extends Controller {
	public function fresh() {
		if (!empty(cms::$db) && !empty(cms::$cache)) {
			trigger_error(__('viennaCMS is already installed!'));
		}
		
		$step = (!empty($this->arguments[0])) ? $this->arguments[0] : intval($_POST['step']);
		
		if (empty($step)) {
			$step = 1;
		}
		
		$this->view['step'] = $step;
		// no, this does not use the form API, that one is not suited for wizards
		
		switch ($step) {
			case 1:
				cms::$layout->view['title'] = __('Welcome');
				
				// TODO: do some requirement checks?
			break;
		}
		
		$this->view['action'] = $this->view->url('install/fresh');
	}
}
