<?php
abstract class Model {
	protected $global;
	
	public function __construct($global) {
		$this->global = $global;
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
			
			switch ($this->fields[$field]['type']) {
				case 'int':
					$value = intval($check);
				break;
				case 'string':
					$value = "'" . $this->global['db']->sql_escape($check) . "'";
				break;
			}
			
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
		}
	}
	
	public function set_row($row) {
		foreach ($row as $field => $value) {
			if (isset($this->fields[$field])) {
				$this->$field = $value;
			}
		}
	}
	
	public function handle_otm() {
		foreach ($this->relations as $key => $parameters) {
			switch ($parameters['type']) {
				case 'one_to_many':
					$mywheres = array();
					foreach ($parameters['my_fields'] as $i => $field) {
						$check = $this->$field;
						
						switch ($this->fields[$field]['type']) {
							case 'int':
								$value = intval($check);
							break;
							case 'string':
								$value = "'" . $this->global['db']->sql_escape($check) . "'";
							break;
						}
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
						$this->$property[$i] = new $name();
						$this->$property[$i]->set_row($row);
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
