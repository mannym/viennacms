<?php
class Form {
	public $callback_object;
	public $action;
	public $form;
	public $form_id;
	public $errors = false;
	public $form_attributes = '';
	/**
	 * Only return fields, not the form tag.
	 * @todo change the name of this property
	 */
	public $return = false;
	
	public function handle_form($form_id, $data) {
		$this->form_id = $form_id;
		$this->form = $data;
		if (empty($this->action)) {
			$this->action = cms::$router->query;
		}
		
		if (isset($_POST[$form_id . '_submit'])) {
			$fields = array();
			foreach ($_POST as $key => $value) {
				if (strpos($key, $form_id) === 0) {
					$fields[str_replace($form_id . '_', '', $key)] = $value;
				}
			}
			
			foreach ($_FILES as $key => $value) {
				if (strpos($key, $form_id) === 0) {
					$fields[str_replace($form_id . '_', '', $key)] = $value;
				}
			}
			
			if (($errors = $this->validate_form($fields)) === false) {
				return $this->save_form($fields);
			} else {
				$this->errors = true;
				return $this->render_form($errors, $fields);
			}
		} else {
			return $this->render_form();
		}
	}
	
	public function validate_form($fields) {
		$errors = array();
		foreach ($this->form['fields'] as $key => $data) {
			$value = $fields[$key];

			if (empty($value) && $data['required']) {
				$errors[$key] = sprintf(__('The field %s is required.'), $data['label']);	
			}
			
			if (isset($data['validate_function'])) {
				$result = call_user_func($data['validate_function'], $value);
				if ($result) {
					$errors[$key] = $result;	
				}
			}
		}
		
		$val_func = $this->form_id . '_validate';
			
		if (method_exists($this->callback_object, $val_func)) {
			$this->callback_object->$val_func($fields, $errors);
		}
		
		if (empty($errors)) {
			return false;	
		}
		
		return $errors;
	}
	
	private function save_form($fields) {
		$function = $this->form_id . '_submit';
		return $this->callback_object->$function($fields);
	}
	
	private function render_form($errors = array(), $values = array()) {
		// sort the fields on weight, this could be more efficient?
		$fields = array();
		$custom_data = array(
			'raw_fields' => array(),
			'fields' => array(),
			'raw_groups' => array(),
			'groups' => array()
		);
		
		foreach ($this->form['fields'] as $name => $settings) {
			if (empty($fields[$settings['weight']])) {
				$fields[$settings['weight']] = array();
			}

			$fields[$settings['weight']] = array_merge($fields[$settings['weight']], array($name => $settings));
		}
		
		ksort($fields);
		$sortedfields = array();
		
		foreach ($fields as $weight => $wfields) {
			foreach ($wfields as $key => $value) {
				$sortedfields[$value['group']][$key] = $value;
			}
		}
		
		$fields = $sortedfields;
		unset($sortedfields);
		
		// render the form...
		$final_fields = '';
		foreach ($fields as $group_id => $rfields) {
			$rendered_fields = '';
			$error = false;
			foreach ($rfields as $key => $value) {
				if (!isset($value['name'])) {
					$value['name'] = $this->form_id . '_' . $key;
				}
				
				$view = new View();
				$view->path = 'form/field_' . $value['type'] . '.php';
				
				foreach ($value as $id => $setting) {
					$view[$id] = $setting;
				}
				
				if (isset($values[$key])) {
					$view['value'] = $values[$key];
				}

				$view['class'] = 'form_' . $this->form_id . '_' . $key;
				
				$content = $view->display();
				$custom_data['raw_fields'][$key] = $content;
				
				if ($value['type'] != 'hidden') {				
					$wrapper = new View();
					$wrapper->path = 'form/field_wrapper.php';
					
					foreach ($value as $id => $setting) {
						$wrapper[$id] = $setting;
					}
					
					if (isset($errors[$key])) {
						$wrapper['error'] = $errors[$key];
						$error = true;
					}
					
					$wrapper['rendered_field'] = $content;

					$field_content = $wrapper->display();

					$rendered_fields .= $field_content;
					$custom_data['fields'][$key] = $field_content;
				} else {
					$custom_data['fields'][$key] = $content;
					$rendered_fields .= $content;
				}
			}
			
			$view = new View();
			$view->path = 'form/field_group.php';
			foreach ($this->form['groups'][$group_id] as $id => $setting) {
				$view[$id] = $setting;
			}
			$view['content'] = $rendered_fields;
			
			if (!empty($errors)) {
				if ($error) {
					$view['expanded'] = true;	
				} else {
					$view['expanded'] = false;
				}
			}

			$group_output = $view->display();
			$custom_data['raw_groups'][$group_id] = $rendered_fields;
			$custom_data['groups'][$group_id] = $group_output;
			$final_fields .= $group_output;
		}
		
		if ($this->return) {
			return $final_fields;
		}
		
		$view = new View();
		$view->path = array(
			'form/form_custom_' . $this->form_id . '.php',
			'admin/simple.php'
		);
		$view['data'] = $final_fields;
		$view['form'] = $this;
		$view['action'] = $view->url($this->action);
		$view['fields'] = $final_fields;
		$view['custom_data'] = $custom_data;

		$output = $view->display();

		$view = new View();
		$view->path = array(
			'form/form_wrapper.php'
		);
		$view['form'] = $this;
		$view['action'] = $view->url($this->action);
		$view['fields'] = $output;
		$view['custom_data'] = $custom_data;
		return $view->display();
	}

	/**
	 * Outputs all fields of a specific type from a field array.
	 *
	 * @param string $type field type to display
	 * @param array $field_array custom_data[fields] style field array
	 * @return string contents of the specific fields
	 */

	public function show_by_type($type, $field_array) {
		if (!is_array($field_array)) {
			return '';
		}

		$output = '';

		foreach ($field_array as $id => $fields) {
			if ($this->form['fields'][$id]['type'] != $type) {
				continue;
			}

			$output .= $fields;
		}

		return $output;
	}
}
