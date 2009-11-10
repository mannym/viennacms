<?php
class Node_Options implements ArrayAccess {
	public $data = array();
	public $node;
	
	public function set($key, $value) {
		if (!isset($this->data[$key]) && !is_a($value, 'Node_Option')) {
			$new = new Node_Option();
			$new->node_id = $this->node->node_id;
			$new->option_name = $key;
			$new->option_value = $value;
			$this->data[$key] = $new;
			$this->node->_options[] = $new;
		} else {
			if (isset($this->data[$key])) {
				$this->data[$key]->option_value = $value;
			} else {
				$this->data[$key] = $value;
			}
		}
	}
	
	public function get($key) {
		if (!isset($this->data[$key])) {
			return '';	
		}
		
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
		$this->data[$key]->delete();
		unset($this->data[$key]);
	}
}
