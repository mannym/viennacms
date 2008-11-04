<?php
class Form {
	public $callback_object;
	public $action;
	private $form;
	public $form_id;
	
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
			
			if (($errors = $this->validate_form($fields)) === false) {
				$this->save_form($fields);	
			} else {
				$this->render_form($errors, $fields);
			}
		} else {
			$this->render_form();
		}
	}
	
	private function validate_form($fields) {
		$errors = array();
		foreach ($fields as $key => $value) {
			$data = $this->form['fields'][$key];

			if (empty($value) && $data['required']) {
				$errors[$key] = sprintf(__('The field %s is required.'), $data['label']);	
			}
			
			$val_func = $this->form_id . '_validate';
			
			if (method_exists($this->callback_object, $val_func)) {
				$result = $this->callback_object->$val_func($key, $value);
				if ($result) {
					$errors[$key] = $result;	
				}
			}
			
			if (isset($data['validate_function'])) {
				$result = call_user_func($data['validate_function'], $value);
				if ($result) {
					$errors[$key] = $result;	
				}
			}
		}
		
		if (empty($errors)) {
			return false;	
		}
		
		return $errors;
	}
	
	private function save_form($fields) {
		$function = $this->form_id . '_submit';
		$this->callback_object->$function($fields);
	}
	
	private function render_form($errors = array(), $values = array()) {
		// sort the fields on weight, this could be more efficient?
		$fields = array();
		
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
				$value['name'] = $this->form_id . '_' . $key;
				
				$view = new View();
				$view->path = 'form/field_' . $value['type'] . '.php';
				
				foreach ($value as $id => $setting) {
					$view[$id] = $setting;
				}
				
				if (isset($values[$key])) {
					$view['value'] = $values[$key];
				}
				
				$content = $view->display();
				
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
					
					$rendered_fields .= $wrapper->display();
				} else {
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
			
			$final_fields .= $view->display();
		}
		
		$view = new View();
		$view->path = 'form/form_wrapper.php';
		$view['form'] = $this;
		$view['action'] = $view->url($this->action);
		$view['fields'] = $final_fields;
		echo $view->display();
	}
}
