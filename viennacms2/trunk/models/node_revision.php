<?php
class Node_Revision extends ADOdb_Active_Record {
	public function Save() {
		if (empty($this->node)) {
			$this->node = $this->node_obj->id;
		}

		$this->node_obj->revision_num++;
		$this->number++;
		$this->time = time();
	
		parent::Save();
		$this->node_obj->update(false);
	}
}