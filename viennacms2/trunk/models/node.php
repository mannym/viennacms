<?php
class Node extends ADOdb_Active_Record {
	public function __construct($table = false, $pkeyarr=false, $db=false) {
		$this->revision = new Node_Revision();
		$this->revision->node_obj = $this;
		
		parent::__construct($table, $pkeyarr, $db);
		
		$this->revision_num = 0;
	}

	public function get_parent() {
		$node = new Node();
		$node->load('id = ?', array($this->parent));
		
		return $node;
	}
	
	public function Set(&$row) {
		parent::Set($row);
		
		$this->revision = new Node_Revision();
		$this->revision->load('node = ? AND number = ?', array($this->id, $this->revision_num));
		$this->revision->node_obj = $this;
	}
	
	public function Insert() {
		$this->created = time();
		parent::Insert();
		$this->revision->save();
	}
	
	public function Update($all = true) {
		if ($all) {
			$this->revision->_saved = false;
			$this->revision->id = null;
			$this->revision->save();
		}
		
		parent::Update();
	}
}