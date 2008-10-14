<?php
class Form {
	public $callback_object;
	private $form;
	private $form_id;
	
	public function handle_form($form_id, $data) {
		$this->form_id = $form_id;
		$this->form = $data;
		
		if (isset($_POST[$form_id . '_submit'])) {
			
		} else {
			$this->render_form();
		}
	}
	
	private function render_form($errors = array()) {
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
			foreach ($rfields as $key => $value) {
				$value['name'] = $this->form_id . '_' . $key;
				
				$view = new View();
				$view->path = 'form/field_' . $value['type'] . '.php';
				
				foreach ($value as $id => $setting) {
					$view[$id] = $setting;
				}
				
				$content = $view->display();
				
				$wrapper = new View();
				$wrapper->path = 'form/field_wrapper.php';
				
				foreach ($value as $id => $setting) {
					$wrapper[$id] = $setting;
				}
				
				$wrapper['rendered_field'] = $content;
				
				$rendered_fields .= $wrapper->display();
			}
			
			$view = new View();
			$view->path = 'form/field_group.php';
			foreach ($this->form['groups'][$group_id] as $id => $setting) {
				$view[$id] = $setting;
			}
			$view['content'] = $rendered_fields;
			$final_fields .= $view->display();
		}
		
		echo $final_fields;
	}
}
