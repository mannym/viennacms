<?php
abstract class Model {
	protected $global;
	public $written = false;

	public function __construct($global) {
		$this->global = $global;
	}
	
	public static function create($name, $global) {
		$model = new $name($global);
		foreach ($model->relations as $parameters) {
			switch ($parameters['type']) {
				case 'one_to_one':
					$property = $parameters['object']['property'];
					$class = $parameters['object']['class'];
					
					$model->$property = new $class($global);
				break;
			}
		}
		
		$model->hook_new();
		
		return $model;
	}
	
	public function read($single = false) {
		$my_id = $this->get_table_name($this->table);
		$this->tables = array($this->get_table_name($this->table) => $this->table);
		$where = array();
		
		foreach ($this->fields as $name => $settings) {
			if (isset($this->$name)) {
				$where[$name] = $this->$name;
			}
		}
		
		$wheres = array();
		
		foreach ($where as $field => $check) {
			if (!empty($this->fields[$field]['relation'])) {
				continue;
			}
			
			$value = $this->sql_value($field);
			
			$wheres[] = $my_id . '.' . $field . ' = ' . $value;
		}
		
		$after_table = '';
		$objects = array();
		
		foreach ($this->relations as $key => $parameters) {
			switch ($parameters['type']) {
				case 'one_to_one':
					$after_table = ' LEFT JOIN ' . $parameters['table'] . ' ' . $this->get_table_name($parameters['table']) . ' ON ';
					$mywheres = array();
					foreach ($parameters['my_fields'] as $i => $field) {
						if (isset($parameters['checks']['other.' . $parameters['their_fields'][$i]]) &&
							!empty($this->{$parameters['checks']['other.' . $parameters['their_fields'][$i]]})) {
							$wheres[] = $this->get_table_name($parameters['table']) . '.' . $parameters['their_fields'][$i] . " = '" . $this->global['db']->sql_escape($this->{$parameters['checks']['other.' . $parameters['their_fields'][$i]]}) . "'";
						} else {
							$mywheres[] = $my_id . '.' . $field . ' = ' . $this->get_table_name($parameters['table']) . '.' . $parameters['their_fields'][$i];
						}
					}
					$after_table .= implode(' AND ', $mywheres);
					
					$objects[] = $parameters['object'];
				break;
			}
		}
	
		$fields = $qtables = array();
				
		foreach ($this->tables as $identifier => $table) {
			$qtables[] = $table . ' ' . $identifier;
		}
		
		$sql  = 'SELECT * FROM ' . implode(', ', $qtables) . $after_table;
		$sql .= ' WHERE ' . implode(' AND ', $wheres);
		$sql .= ($single) ? ' LIMIT 1' : '';
		
		$result = $this->global['db']->sql_query($sql);
		$rowset = $this->global['db']->sql_fetchrowset($result);
		
		if (!$single) {
			$class = get_class($this);
			$return = array();
			
			foreach ($rowset as $i => $row) {
				$return[$i] = new $class($this->global);
				$return[$i]->set_row($row);
				
				foreach ($objects as $parameters) {
					$name = $parameters['class'];
					$property = $parameters['property'];
					
					$return[$i]->$property = new $name($this->global);
					$return[$i]->$property->set_row($row);
				}
				
				$return[$i]->handle_otm();
				$return[$i]->hook_read();
			}
			
			return $return;
		} else {
			$row = $rowset[0];
			
			$this->set_row($row);
			
			foreach ($objects as $parameters) {
				$name = $parameters['class'];
				$property = $parameters['property'];
				
				$this->$property = new $name($this->global);
				$this->$property->set_row($row);
			}
			
			$this->handle_otm();
			$this->hook_read();
		}
	}
	
	public function write() {
		$this->hook_presave();
		
		$where = $end = '';		
		
		if ($this->written) {
			$type = 'UPDATE';
			$sql = 'UPDATE ';
			$wheres = array();
			
			foreach ($this->keys as $key) {
				$wheres[] = $key . ' = ' . $this->sql_value($key);
			}
			
			$where = implode(' AND ', $wheres);
			$end = ' WHERE ' . $where;
		} else {
			$type = 'INSERT';
			$sql = 'INSERT INTO ';
		}
		
		$data = array();
		foreach ($this->fields as $id => $field) {
			$temp = $this->$id;
			settype($temp, $field['type']);
			$data[$id] = $temp;
		}
		
		$sql_data = $this->global['db']->sql_build_array($type, $data);
		$sql .= $this->table . (($type == 'UPDATE') ? ' SET ' : ' ');
		$sql .= $sql_data . $end;
	
		$this->global['db']->sql_query($sql);
		
		$key = $this->keys[0];
		$this->$key = $this->global['db']->sql_nextid();

		$this->hook_save();
		
		foreach ($this->relations as $parameters) {
			$property = $parameters['object']['property'];
			
			switch ($parameters['type']) {
				case 'one_to_one':
					$this->$property->write();
				break;
				case 'one_to_many':
					foreach ($this->$property as $thing) {
						$thing->write();
					}
				break;
			}
		}
		
		$this->written = true;
	}

	protected function hook_read() { }
	protected function hook_presave() { }
	protected function hook_save() { }
	protected function hook_new() { }
	
	public function sql_value($field) {
		switch ($this->fields[$field]['type']) {
			case 'int':
				$value = intval($this->$field);
			break;
			case 'string':
				$value = "'" . $this->global['db']->sql_escape($this->$field) . "'";
			break;
		}
		
		return $value;
	}
	
	public function set_row($row) {
		foreach ($row as $field => $value) {
			if (isset($this->fields[$field])) {
				$this->$field = $value;
			}
		}
		
		$this->written = true;
	}
	
	public function handle_otm() {
		foreach ($this->relations as $key => $parameters) {
			switch ($parameters['type']) {
				case 'one_to_many':
					$mywheres = array();
					foreach ($parameters['my_fields'] as $i => $field) {
						$value = $this->sql_value($field);
						$mywheres[] = $parameters['their_fields'][$i] . ' = ' . $value;
					}
					$where .= implode(' AND ', $mywheres);
					
					$sql = 'SELECT * FROM ' . $parameters['table'] . ' WHERE ' . $where;
					$result = $this->global['db']->sql_query($sql);
					$name = $parameters['object']['class'];
					$property = $parameters['object']['property'];
					$this->$property = array();
					$rowset = $this->global['db']->sql_fetchrowset($result);
					foreach ($rowset as $i => $row) {
						$this->{$property}[$i] = new $name($this->global);
						$this->{$property}[$i]->set_row($row);
					}
				break;
			}
		}
	}
	
	public function get_table_name($table) {
		$output = '';
		$pieces = explode('_', $table);
		
		foreach ($pieces as $piece) {
			$output .= substr($piece, 0, 1);
		}
		
		return $output;
	}
}
