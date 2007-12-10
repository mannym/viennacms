<?php
/**
* viennaCMS node tree system
* "The tree expands, branches, and goes back." -- me :)
* 
* @package viennaCMS
* @author viennainfo.nl
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

define('NODE_SINGLE', 1);
define('NODE_CHILDREN',	2);
define('NODE_SIBLINGS', 3);
define('NODE_PARENT', 4);
define('NODE_SIBLINGS_ALL', 5);
define('NODE_TITLE', 6);

class CMS_Node {
	public $node_id;
	public $title;
	public $title_clean;
	public $description;
	public $created;
	public $parent_id;
	public $type;
	public $revision;
	public $revision_number;
	public $parentdir;
	public $extension;
		
	public $options = array();
	
	private $dbfields = array('node_id', 'title', 'description', 'title_clean', 'created', 'parent_id', 'type', 'revision_number', 'parentdir', 'extension');
	
	static public function getnew() {
		$return = new CMS_Node();
		$return->revision = new Node_Revision();
		$return->revision->read_modules();
		
		return $return;
	}
	
	public function read($what = NODE_SINGLE) {
		global $nodecache;
		if (!isset($nodecache)) {
			$nodecache = array();
		}
		
		$db = database::getnew();
		$row = array();
			
		switch ($what) {
			case NODE_SINGLE:
				$sql_where = 'node_id = ' . intval($this->node_id);
			break;
			case NODE_CHILDREN:
				$sql_where = 'parent_id = ' . intval($this->node_id);
			break;
			case NODE_SIBLINGS:
				$sql_where = 'parent_id = ' . intval($this->parent_id);
				$sql_where .= ' AND node_id <> ' . intval($this->node_id);
			break;
			case NODE_SIBLINGS_ALL:
				$sql_where = 'parent_id = ' . intval($this->parent_id);
			break;
			case NODE_PARENT:
				$sql_where = 'node_id = ' . intval($this->parent_id);
			break;
			case NODE_TITLE:
				$sql_where = "title_clean = '" . $db->sql_escape($this->title_clean) . "' 
				AND parentdir = '{$this->parentdir}' 
				AND extension = '{$this->extension}'";
				$what = NODE_SINGLE;
			break;
		}
		
		$sql = 'SELECT * FROM ' . NODES_TABLE . ' WHERE ' . $sql_where;
		if (isset($nodecache[md5($sql)])) {
			$rowset = $nodecache[md5($sql)];
		} else {
			$result = $db->sql_query($sql);
			$rowset = $db->sql_fetchrowset($result);
			$nodecache[md5($sql)] = $rowset;
		}
		
		if (count($rowset) == 1 && $what == NODE_SINGLE) {
			$row = $rowset[0];
			//$this->dbfields = array();
			$class = new ReflectionClass('CMS_Node');
			$properties = $class->getProperties();
			
			foreach ($properties as $property) {
				$name = $property->getName();
				
				if (isset($row[$name])) {
					$this->$name = $row[$name];
					//$this->dbfields[] = $name;
				}
			}
			
			$this->revision = new Node_Revision();
			$this->revision->node_id = $this->node_id;
			$this->revision->revision_number = $this->revision_number;
			$this->revision->read();
			
			$sql  = 'SELECT * FROM ' . NODE_OPTIONS_TABLE;
			$sql .= ' WHERE node_id = ' . intval($this->node_id);
			
			if (isset($nodecache[md5($sql)])) {
				$orowset = $nodecache[md5($sql)];
			} else {
				$result = $db->sql_query($sql);
				$orowset = $db->sql_fetchrowset($result);
				$nodecache[md5($sql)] = $orowset;
			}
			
			foreach ($orowset as $orow) {
				$this->options[$orow['option_name']] = $orow['option_value'];
			}
			
			$this->prepare();
			
			return true;
		} else if (count($rowset) > 1 || $what != NODE_SINGLE) {
			$return = array();
			$i = 0;
			
			foreach ($rowset as $row) {
				$return[$i] = new CMS_Node();
				
				$class = new ReflectionClass('CMS_Node');
				$properties = $class->getProperties();
				foreach ($properties as $property) {
					$name = $property->getName();
				
					if (isset($row[$name])) {
						$return[$i]->$name = $row[$name];
						//$return[$i]->dbfields[] = $name;
					}
				}
				
				$return[$i]->revision = new Node_Revision();
				$return[$i]->revision->node_id = $return[$i]->node_id;
				$return[$i]->revision->revision_number = $return[$i]->revision_number;
				$return[$i]->revision->read();
				
				$sql  = 'SELECT * FROM ' . NODE_OPTIONS_TABLE;
				$sql .= ' WHERE node_id = ' . intval($return[$i]->node_id);
				
				if (isset($nodecache[md5($sql)])) {
					$orowset = $nodecache[md5($sql)];
				} else {
					$result = $db->sql_query($sql);
					$orowset = $db->sql_fetchrowset($result);
					$nodecache[md5($sql)] = $orowset;
				}
				
				foreach ($orowset as $orow) {
					$return[$i]->options[$orow['option_name']] = $orow['option_value'];
				}
				
				$return[$i]->prepare();
				
				$i++;
			}
			
			return $return;
		}
		
		return false;
	}
	
	public function write($all = true) {
		$db = database::getnew();
		
		if (!empty($this->node_id)) {
			$sql = 'UPDATE ';
			$sql_end = ' WHERE node_id = ' . intval($this->node_id);
		} else {
			$sql = 'INSERT INTO ';
			$sql_end = '';
		}
		
		$sql .= NODES_TABLE . ' SET ';
		
		$dbfields = utils::remove_array($this->dbfields, 'node_id');
		
		$class = new ReflectionClass('CMS_Node');
		$properties = $class->getProperties();
		
		foreach ($properties as $property) {
			$name = $property->getName();
			
			if (in_array($name, $dbfields)) {
				$sql .= $name . " = '" . $db->sql_escape($this->$name) . "', "; 
			}
		}
		
		$sql = substr($sql, 0, -2);
		$sql .= $sql_end;

//		echo $sql . "<br />";
		
		$db->sql_query($sql);
		
		if (empty($this->node_id)) {
			$this->node_id = $db->sql_nextid();
		}
		
		// and now add/update the revision :)
		if ($all) {
			$this->revision->node_id = $this->node_id;
			$this->revision_number = $this->revision->write();

			foreach ($this->options as $name => $value) {
				$this->write_option($name, $value);
			}
			
			$this->write(false);
		}
	}
	
	public function prepare() {
		if (empty($this->title_clean)) {
			$this->title_clean = utils::clean_title($this->title);
			$this->write(false);
		}
		
	}
	
	public function write_option($name, $value, $add = false) {
		$db = database::getnew();
		
		$sql  = 'UPDATE ' . NODE_OPTIONS_TABLE . ' SET ';
		$sql .= "option_value = '" . $db->sql_escape($value) . "' ";
		$sql .= "WHERE option_name = '" . $db->sql_escape($name) . "' AND ";
		$sql .= 'node_id = ' . intval($this->node_id);
		
		if ($add) {
			$sql  = 'INSERT INTO ' . NODE_OPTIONS_TABLE . ' SET ';
			$sql .= "option_value = '" . $db->sql_escape($value) . "', ";
			$sql .= "option_name = '" . $db->sql_escape($name) . "', ";
			$sql .= 'node_id = ' . intval($this->node_id);
		}
		return $db->sql_query($sql);
	}
	
	public function get_children() {
		return $this->read(NODE_CHILDREN);
	}
	
	public function get_siblings() {
		return $this->read(NODE_SIBLINGS);
	}

	public function get_siblings_all() {
		return $this->read(NODE_SIBLINGS_ALL);
	}
	
	public function get_parent() {
		return $this->read(NODE_PARENT);
	}
	
	public function dump() {
		$class = new ReflectionClass('CMS_Node');
		$properties = $class->getProperties();
		
		echo '<pre>';
		
		foreach ($properties as $property) {
			$name = $property->getName();
			
			if (isset($this->$name) && in_array($name, $this->dbfields)) {
				echo $name . ' = ' . $this->$name . "\r\n";
			}
		}
		
		echo '</pre>';
	}
}

class Node_Revision {
	public $revision_id;
	public $node_id;
	public $revision_number = 0;
	public $node_content;
	public $revision_date;
	public $modules = array();
	
	private $dbfields = array('revision_id', 'node_id', 'revision_number', 'node_content', 'revision_date');
	
	public function read() {
		global $revcache;
		if (!isset($revcache)) {
			$revcache = array();
		}
		
		$db = database::getnew();
		$row = array();

		if (!empty($this->node_id) && !empty($this->revision_number)) {
			$sql_where = 'node_id = ' . intval($this->node_id);
			$sql_where .= ' AND revision_number = ' . intval($this->revision_number);
		}
		
		if (!empty($this->revision_id)) {
			$sql_where = 'revision_id = ' . intval($this->revision_id);
		}
		
		if (empty($sql_where)) {
			$this->read_modules(); // to get the default module list
			return false;
		}
		
		$sql = 'SELECT * FROM ' . NODE_REVISIONS_TABLE . ' WHERE ' . $sql_where;
		if (isset($revcache[md5($sql)])) {
			$rowset = $revcache[md5($sql)];
		} else {
			$result = $db->sql_query($sql);
			$rowset = $db->sql_fetchrowset($result);
			$revcache[md5($sql)] = $rowset;
		}
		
		$row = $rowset[0];
		$this->dbfields = array();
		$class = new ReflectionClass('Node_Revision');
		$properties = $class->getProperties();
		
		foreach ($properties as $property) {
			$name = $property->getName();
			
			if (isset($row[$name])) {
				$this->$name = $row[$name];
				$this->dbfields[] = $name;
			}
		}
		
		$this->read_modules();
	}
	
	public function read_modules() {
		$this->modules = unserialize($this->node_content);
		if (!$this->modules || is_null($this->modules)) {
			$this->modules = array(
				'left' => array(),
				'middle' => array(
					array(
						'extension' => 'core',
						'module' => 'html_content',
						'content' => __('This node has no content. Add content in the administration panel.'),
						'content_title' => __("Edit this content title"),
					),
				),
				'right' => array()
			);
		}
	}
	
	public function write() {
		$this->revision_number++;
		$this->revision_date = time();
		$this->revision_id = '';
		$this->node_content = serialize($this->modules);
		$this->_write();
		
		return $this->revision_number;
	}
	
	private function _write() {
		$db = database::getnew();
		
		if (!empty($this->revision_id)) {
			$sql = 'UPDATE ';
			$sql_end = ' WHERE revision_id = ' . intval($this->revision_id);
		} else {
			$sql = 'INSERT INTO ';
			$sql_end = '';
		}
		
		$sql .= NODE_REVISIONS_TABLE . ' SET ';
		
		$dbfields = utils::remove_array($this->dbfields, 'revision_id');
		
		$class = new ReflectionClass('Node_Revision');
		$properties = $class->getProperties();
		
		foreach ($properties as $property) {
			$name = $property->getName();
			
			if (in_array($name, $dbfields)) {
				$sql .= $name . " = '" . $db->sql_escape($this->$name) . "', "; 
			}
		}
		
		$sql = substr($sql, 0, -2);
		$sql .= $sql_end;
		
//		echo $sql . "<br />";
		
		$db->sql_query($sql);
		
		return $db->sql_nextid();
	}

	public function dump() {
		$class = new ReflectionClass('Node_Revision');
		$properties = $class->getProperties();
		
		echo '<pre>';
		
		foreach ($properties as $property) {
			$name = $property->getName();
			
			if (isset($this->$name) && in_array($name, $this->dbfields)) {
				echo $name . ' = ' . $this->$name . "\r\n";
			}
		}
		
		echo '</pre>';
	}	
}
?>