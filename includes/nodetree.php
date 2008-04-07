<?php
/**
* viennaCMS node tree system
* "The tree expands, branches, and goes back." -- me :)
* 
* @package viennaCMS
* @author viennacms.nl
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

define('NODE_SINGLE', 1);
define('NODE_CHILDREN',	2);
define('NODE_SIBLINGS', 3);
define('NODE_PARENT', 4);
define('NODE_SIBLINGS_ALL', 5);
define('NODE_TITLE', 6);
define('NODE_TITLEC', 7);

/**
 * CMS_Node
 *
 * @package viennaCMS
 * @author viennaCMS developers, original design by Bas
 * @copyright 2008
 * @version $Id$
 * @access public
 */
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
	public $node_order;
		
	public $options = array();
	
	private $dbfields = array('node_id', 'title', 'description', 'title_clean', 'created', 'parent_id', 'type', 'revision_number', 'parentdir', 'extension', 'node_order');
	
  /**
   * CMS_Node::getnew()
   *
   * @return CMS_Node new CMS_Node object
   */
	static public function getnew() {
		$return = new CMS_Node();
		$return->revision = new Node_Revision();
		$return->revision->read_modules();
		
		return $return;
	}
	
  /**
   * CMS_Node::read()
   *
   * @param mixed $what what to grab?
   * @param mixed $use_cache false for no caching, int for time
   * @return mixed array with nodes, or a single node
   */
	public function read($what = NODE_SINGLE, $use_cache = false) {
		$db = database::getnew();
		$row = array();
			
		switch ($what) {
			case NODE_SINGLE:
				$sql_where = 'node_id = ' . intval($this->node_id);
			break;
			case NODE_CHILDREN:
				$sql_where = 'parent_id = ' . intval($this->node_id) . ' ORDER BY node_order ASC, title ASC';
			break;
			case NODE_SIBLINGS:
				$sql_where = 'parent_id = ' . intval($this->parent_id);
				$sql_where .= ' AND node_id <> ' . intval($this->node_id) . ' ORDER BY node_order ASC, title ASC';
			break;
			case NODE_SIBLINGS_ALL:
				$sql_where = 'parent_id = ' . intval($this->parent_id) . ' ORDER BY node_order ASC, title ASC';
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
			case NODE_TITLEC:
				$sql_where = "title_clean = '" . $db->sql_escape($this->title_clean) . "'";
				$what = NODE_SINGLE;
			break;
		}

		$sql = 'SELECT * FROM ' . NODES_TABLE . ' WHERE ' . $sql_where;
		
		if (!$use_cache) {
			$result = $db->sql_query($sql);
			$rowset = $db->sql_fetchrowset($result);
		} else {
			global $cache;
			$result = $cache->sql_load($sql);
			if (!$result) {
				$result = $db->sql_query($sql);
				$cache->sql_save($sql, $result, $use_cache);
			}
			$rowset = $cache->sql_fetchrowset($result);
		}
		
		utils::get_types();
		
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
			
			if (utils::$types[$this->type]['type'] != NODE_NO_REVISION) {
				$this->revision = new Node_Revision();
				$this->revision->node_id = $this->node_id;
				$this->revision->revision_number = $this->revision_number;
				$this->revision->read($this);
			}
			
			$sql  = 'SELECT * FROM ' . NODE_OPTIONS_TABLE;
			$sql .= ' WHERE node_id = ' . intval($this->node_id);
			
			$result = $db->sql_query($sql);
			$orowset = $db->sql_fetchrowset($result);
						
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
				if (utils::$types[$return[$i]->type]['type'] != NODE_NO_REVISION) {
					$return[$i]->revision = new Node_Revision();
					$return[$i]->revision->node_id = $return[$i]->node_id;
					$return[$i]->revision->revision_number = $return[$i]->revision_number;
					$return[$i]->revision->read($return[$i]);
				}
				
				$sql  = 'SELECT * FROM ' . NODE_OPTIONS_TABLE;
				$sql .= ' WHERE node_id = ' . intval($return[$i]->node_id);
				
				$result = $db->sql_query($sql);
				$orowset = $db->sql_fetchrowset($result);
				
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
	
  /**
   * CMS_Node::write()
   *
   * @param bool $all internal use only, write all
   * @return void
   */
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
		
		$db->sql_query($sql);
		
		if (empty($this->node_id)) {
			$this->node_id = $db->sql_nextid();
		}
		
		// and now add/update the revision :)
		if ($all) {
			utils::get_types();
			$type = utils::$types[$this->type];
			if ($type['type'] != NODE_NO_REVISION) {
				$this->revision->node_id = $this->node_id;
				$this->revision_number = $this->revision->write();
			}
			
			foreach ($this->options as $name => $value) {
				$sql = "SELECT * FROM " . NODE_OPTIONS_TABLE . "
				WHERE node_id = " . $this->node_id . "
				AND option_name = '" . $name . "'";
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$add = ($row === false) ? true : false;
				$this->write_option($name, $value, $add);
			}
			
			$this->write(false);
		}
	}
	
  /**
   * CMS_Node::prepare()
   *
   * @return void
   */
	public function prepare() {
		if (empty($this->title_clean)) {
			$this->title_clean = utils::clean_title($this->title);
			$this->write(false);
		}
		
	}
	
  /**
   * CMS_Node::write_option()
   *
   * @param string $name option name
   * @param string $value option value
   * @param bool $add create or replace
   * @return bool query result
   */
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
	
  /**
   * CMS_Node::get_children()
   *
   * @return array children of current node
   */
	public function get_children() {
		return $this->read(NODE_CHILDREN, false);
	}
	
  /**
   * CMS_Node::get_siblings()
   *
   * @return array siblings of current node
   */
	public function get_siblings() {
		return $this->read(NODE_SIBLINGS);
	}

  /**
   * CMS_Node::get_siblings_all()
   *
   * @return array siblings of current node, and current node
   */
	public function get_siblings_all() {
		return $this->read(NODE_SIBLINGS_ALL);
	}
	
  /**
   * CMS_Node::get_parent()
   *
   * @return CMS_Node parent of current node
   */
	public function get_parent() {
		return $this->read(NODE_PARENT);
	}
	
  /**
   * CMS_Node::dump()
   *
   * @return void
   */
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

/**
 * Node_Revision
 *
 * @package viennaCMS
 * @author viennaCMS Developers
 * @copyright 2008
 * @version $Id$
 * @access public
 */
class Node_Revision {
	private $mynode;
	public $has_modules = true;
	public $revision_id;
	public $node_id;
	public $revision_number = 0;
	public $node_content;
	public $revision_date;
	public $modules = array();
	
	private $dbfields = array('revision_id', 'node_id', 'revision_number', 'node_content', 'revision_date');
	
  /**
   * Node_Revision::read()
   *
   * @param mixed $node node to read the revision for
   * @return
   */
	public function read($node) {
		global $revcache;
		if (!isset($revcache)) {
			$revcache = array();
		}
		
		$this->mynode = $node;
		
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
	
  /**
   * Node_Revision::read_modules()
   *
   * @return
   */
	public function read_modules() {
		utils::get_types();
		if (utils::$types[$this->mynode->type]['type'] == NODE_MODULES) {
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
		} else {
			$this->has_modules = false;
		}
	}
	
  /**
   * Node_Revision::write()
   *
   * @return
   */
	public function write() {
		$this->revision_number++;
		$this->revision_date = time();
		$this->revision_id = '';
		if ($this->has_modules) {
			$this->node_content = serialize($this->modules);
		}
		$this->_write();
		
		return $this->revision_number;
	}
	
  /**
   * Node_Revision::_write()
   *
   * @return
   */
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
		
		$db->sql_query($sql);
		
		return $db->sql_nextid();
	}

  /**
   * Node_Revision::dump()
   *
   * @return
   */
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