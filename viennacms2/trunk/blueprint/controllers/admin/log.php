<?php
class AdminLogController extends Controller {
	public function index() {
		$query = $this->arguments[0];
		parse_str($query, $values);
		
		$log = new VLogItem();
		$log->order = array('log_time' => 'desc');
		
		foreach ($values as $key => $value) {
			if (substr($key, 0, 4) != 'log_') {
				continue;
			}
			
			$log->$key = $value;
		}
		
		$logs = $log->read();
		
		$this->view['logs'] = $logs;
		$types = array();
		$sources = array();
		
		foreach ($logs as $log) {
			if (!in_array($log->log_type, $types)) {
				$types[] = $log->log_type;
			}
			
			if (!in_array($log->log_source, $sources)) {
				$sources[] = $log->log_source;
			}
		}
		
		$this->view['sources'] = $sources;
		$this->view['types'] = $types;
		
		admincontroller::set_context('log', false);
	}
	
	public function clear() {
		$log = new VLogItem();
		$logs = $log->read();
		
		foreach ($logs as $log) {
			$log->delete();
		}
		
		cms::log('core', 'The log has been cleared.', 'info');
		cms::redirect('admin/controller/log/index');
	}
}