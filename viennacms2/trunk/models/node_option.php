<?php
class Node_Option extends ADOdb_Active_Record {
	public function __toString() {
		return $this->value;
	}
}

class Node_Options implements ArrayAccess {
	public $data = array();
	public $node;
	
	public function set($key, $value) {
		if (!isset($this->data[$key]) && !is_a($value, 'Node_Option')) {
			$new = new Node_Option();
			$new->node = $this->node->id;
			$new->name = $key;
			$new->value = $value;
			$this->data[$key] = $new;
		} else {
			if (isset($this->data[$key])) {
				$this->data[$key]->value = $value;
			} else {
				$this->data[$key] = $value;
			}
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
		unset($this->data[$key]);
	}
}
