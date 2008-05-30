<?php
/**
* Form API for viennaCMS
* "It appears that you entered a number where it doesn't belong!"
* 
* @package viennaCMS
* @author viennacms.nl
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

/**
* @ignore
*/

if (!defined('IN_VIENNACMS')) {
	exit;
}

class formapi {
	/**
	 * Gets and fully handles a form. 
	 *
	 * @param $form object form object
	 */
	function get_form($form) {
		$id = $form->form_id;
		
		if (isset($_POST['submit_' . $id])) {
			$fields = $this->get_form_fields($id);
			
			$result = $this->validate_form($form, $fields);
			if (is_string($result)) {
				return $result;
			}
		} else {
			return $this->display_form($form);
		}
	}
	
	function get_form_fields($id) {
		$regex = '/^' . preg_quote($id, '/') . '_/';
		$return = array();
		
		foreach ($_POST as $key => $value) {
			if (preg_match($regex, $key)) {
				$return[preg_replace($regex, '', $key)] = $value;
			}
		}
		
		return $return;
	}
	
	function validate_form($form, &$fields) {
		$errors = array();
		foreach ($form->elements as $section) {
			foreach ($section as $key => $value) {
				if ($value['required'] && (!count($fields[$key]) || (is_string($fields[$key]) && strlen(trim($fields[$key])) == 0))) {
					$errors[$key] = sprintf(__('%s field is required.'), $value['title']);
					continue;
				}
				
				if (isset($value['max_length']) && strlen($fields[$key]) > $value['max_length']) {
					$errors[$key] = sprintf(__('%s cannot be longer than %d characters.'), $value['title'], $value['max_length']);
					continue;
				}
				
				if (method_exists($form, 'validate')) {
					$result = $form->validate($key, $fields[$key]);
					if (is_string($result)) {
						$errors[$key] = $result;
					}
				}
			}
		}
		
		//var_dump($errors);
		
		if (!empty($errors)) {
			return $this->display_form($form, $errors, $fields);
		}
		
		$form->validated = true;
		return true;
	}
	
	function display_form($form, $errors = array(), $errf = array()) {
		$api = new formapi_base();
		$return = '';
		foreach ($form->elements as $title => $items) {
			$api->id = $form->form_id;
			$api->setformfields($items);
			$api->seterrors($errors);
			$api->errfields = $errf;
			$api->title = $title;
			$api->action = $form->action;
			$api->submit = __('Submit');
			$api->generateform();
			$return .= $api->content;
		}
		
		return $return;
	}
}

class formapi_base {
	public $textareas = array();
	public $textfields = array();
	public $hiddenfields = array();
	public $selectboxes = array();
	public $place = array();
	public $submit;
	public $action;
	public $content;
	public $title;
	public $id;
	public $errfields = array();
	private $errors = array();
	private $position = 0;
	
	function setformfields($formfields) {
		foreach($formfields as $formfield) {
			$this->_add_formfield($formfield); // Add the formfield
		}
		return;
	}
	
	function seterrors($errors) {
		$this->errors = $errors;
	}
	
	public function _add_formfield($formfield) {
		$type 	= $formfield['type'];
		
		// special type handling
		switch ($type) {
			case 'language':
				$this->_add_language($formfield);
				return;
			break;
			case 'template':
				$this->_add_template($formfield);
				return;
			break;
		}
		
		$name 	= $this->id . '_' . $formfield['name'];
		//var_dump($this->id);
		if(isset($formfield['value'])) // In case of radio button or selectbox, we have 'values' for that.
		{
			$value	= isset($formfield['value']) ? $formfield['value'] : ''; // Not required
		}
		elseif(isset($formfield['values']))
		{
			$value = $formfield['values'];
		}
		$title	= isset($formfield['title']) ? $formfield['title'] : ''; // Not required for hidden formfields
		$desc	= isset($formfield['description']) ? $formfield['description'] : ''; // Not required for hidden formfields
		$reqd 	= (isset($formfield['required'])) ? $formfield['required'] : false;
		
		switch ($type) {
			case 'selectbox':
			case 'radio':
			case 'textarea': // Set the place of the formfield. Not for hidden or submit:
			case 'textfield':
				$this->position++;
				$this->place[$this->position]['name'] = $name;
				$this->place[$this->position]['type'] = $type;					
			break;
		}
		
		switch($type) {
			case 'textarea': // if type is textarea, add it to textareas
				$this->textareas[$name] = array(
					'name' 			=> $name,
					'title'			=> $title,
					'value'			=> $value,
					'description'	=> $desc,
					'required'		=> $reqd
					// 'place'			=> $place, We don't do anything with this, do we?
				);
			break;
			
			case 'textfield': // if type is textfield, add it to textfields
				$this->textfields[$name] = array(
					'name' 			=> $name,
					'title'			=> $title,
					'value'			=> $value,
					'description'	=> $desc,
					'required'		=> $reqd
					// 'place'			=> $place, We don't do anything with this, do we?
				);
			break;
			
			case 'selectbox':
				$this->selectboxes[$name] = array(
					'name'			=> $name,
					'title'			=> $title,
					'description'	=> $desc,
					'values'		=> $value,
					'required'		=> $reqd
				);
			break;

			case 'radio':
				$this->radios[$name] = array(
					'name'			=> $name,
					'title'			=> $title,
					'description'	=> $desc,
					'values'		=> $value,
					'required'		=> $reqd,
					'sameline'		=> isset($formfield['sameline']) ? $formfield['sameline'] : true,
				);
			break;
			
			case 'hidden':
				$this->hiddenfields[$name] = array(
					'name'	=> $name,
					'value'	=> $value,
				);				
			break;
			
			case 'submit':
				$this->submit	= $value;
			break;
							
		}
		return;
	}
	
	public function _add_language($formfield) {
		$formfield['type'] = 'selectbox';
		$current = $formfield['value'];
		$formfield['value'] = array(
			'' => array(
				'title' => '-- Select --',
				'selected' => false
			)
		);
		$languages = scandir(ROOT_PATH . 'locale');
		foreach($languages as $language) {
			if (is_dir(ROOT_PATH . 'locale/' . $language) && is_dir(ROOT_PATH . 'locale/' . $language . '/LC_MESSAGES/') && file_exists(ROOT_PATH . 'locale/' . $language . '/LC_MESSAGES/viennacms.mo'))
			{
				$formfield['value'][$language] = array(
					'title' => $language,
					'selected' => ($current == $language)
				);
			} 
		}
		// and add the new, improved, formfield.
		$this->_add_formfield($formfield);
	}
	
	public function _add_template($formfield) {
		$formfield['type'] = 'selectbox';
		$current = $formfield['value'];
		$formfield['value'] = array(
			'' => array(
				'title' => '-- Select --',
				'selected' => false
			)
		);
		
		$templates = scandir(ROOT_PATH . 'styles');
		foreach($templates as $template) {
			if (is_dir(ROOT_PATH . 'styles/' . $template) && file_exists(ROOT_PATH . 'styles/' . $template . '/index.php') && file_exists(ROOT_PATH . 'styles/' . $template . '/module.php'))
			{
				$formfield['value'][$template] = array(
					'title' => $template,
					'selected' => ($current == $template)
				);
			} 
		}

		// and add the new, improved, formfield.
		$this->_add_formfield($formfield);
	}
	
	function generateform() {
		$this->content = <<<CONTENT
<form action="$this->action" method="post">
	<fieldset>
		<legend>$this->title</legend>
CONTENT;
		
		foreach ($this->place as $place) {
			switch ($place['type']) {
				case 'textarea':
					$this->_generate_textarea($place['name']);
				break;
				case 'textfield':
					$this->_generate_textfield($place['name']);
				break;
				case 'selectbox':
					$this->_generate_selectbox($place['name']);
				break;
				case 'radio':
					$this->_generate_radio($place['name']);
				break;
			}
		}
		
		foreach ($this->hiddenfields as $field) {
			$this->_generate_hidden_formfield($field['name']);
		}
		
		$this->content .= <<<CONTENT
		<input type="submit" name="submit_{$this->id}" value="$this->submit" /> 
	</fieldset>
</form>					
CONTENT;
	}
	
	function _generate_element($field, $value) {
		$name = preg_replace('/^' . $this->id . '_/', '', $field['name']);
		
		$error = (isset($this->errors[$name])) ? $this->errors[$name] : '';
		if (!empty($error)) {
			$error = '<span class="formfield-error">' . $error . '</span>';
		}
		
		$required = ($field['required']) ? '<span class="formfield-required" title="' . __('This form field is required.') . '">*</span>' : '';
		
		$content = <<<CONTENT
		<strong><label for="{$field['name']}">{$field['title']}</label>: </strong>{$required}<br />
		{$value} {$error}<br />
		<span class="formfield-description">{$field['description']}</span><br />
CONTENT;
		return $content;
	}
	
	function _generate_textfield($textfield_name) {
		$textfield 		= $this->textfields[$textfield_name];
		$name	 		= $textfield['name'];
		$title			= $textfield['title'];
		$description	= $textfield['description'];
		$value			= $textfield['value'];
		if (isset($_POST[$name])) {
			$value = $_POST[$name];
		}
		
		$content = <<<CONTENT
		<input type="text" name="$name" id="$name" value="$value" />
CONTENT;
		$content = $this->_generate_element($textfield, $content);
	$this->content .= $content;
	return;		
	}
	
	function _generate_textarea($textarea_name) {
		$textarea 		= $this->textareas[$textarea_name];
		$name	 		= $textarea['name'];
		$title			= $textarea['title'];
		$description	= $textarea['description'];
		$value			= $textarea['value'];
		if (isset($_POST[$name])) {
			$value = $_POST[$name];
		}
		
		$content = <<<CONTENT
		<textarea cols="30" rows="4" name="$name" id="$name">$value</textarea>
CONTENT;
		$content = $this->_generate_element($textarea, $content);
	$this->content .= $content;
	return;		
	}
	
	function _generate_radio($name) {
		$item = $this->radios[$name];
		
		$inputs = '';
		foreach ($item['values'] as $key => $value) {
			if (isset($_POST[$name]) && $_POST[$name] == $key) {
				$value['selected'] = true;
			}
			$selected = ($value['selected']) ? ' checked="checked"' : '';
			
			$inputs .= '<label><input type="radio" name="' . $item['name'] . '" value="' . $key . '"' . $selected . ' />';
			$inputs .= $value['title'];
			$inputs .= '</label>';
			if(!$item['sameline'])
			{
				$inputs .= '<br />';			
			}
			else {
				$inputs .= " &nbsp; &nbsp;";
			}
		}
		
		$this->content .= $this->_generate_element($item, $inputs);
	}
	
	function _generate_selectbox($name) {
		$item = $this->selectboxes[$name];
		
		$selectbox = '<select id="' . $item['name'] . '" name="' . $item['name'] . '">';
		foreach ($item['values'] as $key => $value) {
			if (isset($_POST[$name]) && $_POST[$name] == $key) {
				$value['selected'] = true;
			}
			$selected = ($value['selected']) ? ' selected="selected"' : '';
			$selectbox .= '<option value="' . $key . '"' . $selected . '>' . $value['title'] . '</option>';
		}
		$selectbox .= '</select>';
		
		$this->content .= $this->_generate_element($item, $selectbox);
	}
	
	function _generate_hidden_formfield($hidden_formfield_name) {
		$hidden_formfield	= $this->hiddenfields[$hidden_formfield_name];
		$name				= $hidden_formfield['name'];
		$value				= $hidden_formfield['value'];
		
		$content = <<<CONTENT
			<input type="hidden" name="$name" value="$value" />
CONTENT;
		$this->content .= $content;
		return;
	}
}

class form {
	public $validated;
	public $elements;
	public $action;
	public $form_id;
}
?>