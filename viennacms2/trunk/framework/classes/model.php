<?php
abstract class Model {
	protected $global;
	
	public function __construct($global) {
		$this->global = $global;
	}
	
	public function read() {
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
				break;
			}
		}
	
		$fields = $qtables = array();
				
		foreach ($this->tables as $identifier => $table) {
			$qtables[] = $table . ' ' . $identifier;
		}
		
		$sql  = 'SELECT * FROM ' . implode(', ', $qtables) . $after_table;
		$sql .= ' WHERE ' . implode(' AND ', $wheres);
		
		$result = $this->global['db']->sql_query($sql);
		var_dump($sql);
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
