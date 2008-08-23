<?php
class Node extends ADOdb_Active_Record {
	public function get_parent() {
		$node = new Node();
		$node->load('id = ?', $this->parent);
		
		return $node;
	}
}