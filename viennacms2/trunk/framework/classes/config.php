<?php
class Config implements ArrayAccess {
	public $data = array();
	public $node;
	private $_options = array();
	
	public function __construct() {
		$node_options = new Node_Option();
		$node_options->node_id = 0;
		$this->_options = $node_options->read();
		
		foreach ($this->_options as $option) {
			$this->data[$option->option_name] = $option;
		}
	}
	
	public function set($key, $value) {
		if (!isset($this->data[$key]) && !is_a($value, 'Node_Option')) {
			$new = new Node_Option();
			$new->node_id = 0;
			$new->option_name = $key;
			$new->option_value = $value;
			$new->write();
			$this->data[$key] = $new;
			$this->_options[] = $new;
		} else {
			if (isset($this->data[$key])) {
				$this->data[$key]->option_value = $value;
			} else {
				$this->data[$key] = $value;
			}
			
			$this->data[$key]->write();
		}
	}
	
	public function get($key) {
		return $this->data[$key];
	}
	
	public function offsetExists($key) {
		return (isset($this->data[$key]));
	}
	
	public function offsetGet($key) {
		return $this->get($key);
	}
	
	public function offsetSet($key, $value) {
		$this->set($key, $value);
	}
	
	public function offsetUnset($key) {
		if (isset($this->data[$key])) {
			$this->data[$key]->delete();
		}
		
		unset($this->data[$key]);
	}
}
