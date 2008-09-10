<?php
class Node extends ADOdb_Active_Record {
	public function __construct($table = false, $pkeyarr=false, $db=false) {
		$this->revision = new Node_Revision();
		$this->revision->node_obj = $this;
		
		$options = new Node_Option();
		$this->options = new Node_Options();
		$this->options->node = $this;
		
		parent::__construct($table, $pkeyarr, $db);
		
		$this->revision_num = 0;
	}

	public function get_parent() {
		$node = new Node();
		$node->load('node_id = ?', array($this->parent));
		
		return $node;
	}
	
	public function get_children() {
		$node = new Node();
		return $node->find('parent = ?', array($this->node_id));
	}
	
	public function get_siblings_all() {
		$node = new Node();
		return $node->find('parent = ?', array($this->parent));
	}
	
	public function Set(&$row) {
		parent::Set($row);
		
		// get revision
		$this->revision = new Node_Revision();
		$this->revision->content = '';
		$this->revision->load('node = ? AND number = ?', array($this->node_id, $this->revision_num));
		$this->revision->node_obj = $this;
		
		// get options
		$options = new Node_Option();
		$this->options = new Node_Options();
		$this->options->node = $this;
		$options = $options->find('node_id = ?', array($this->node_id));
		foreach ($options as $key => $value) {
			$this->options[$value->option_name] = $value;
		}
	}
	
	public function Save() {
		parent::Save();
		
		foreach ($this->options->data as $option) {
			$option->save();
		}
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