<?php
/**
* Form extension for the viennaCMS
* 
* @package viennaCMS
* @author viennacms.nl
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*/

if (!defined('IN_VIENNACMS')) {
	exit;
}

class extension_form {
	public $textareas = array();
	public $textfields = array();
	public $hiddenfields = array();
	public $selectboxes = array();
	public $place = array();
	public $submit;
	public $action;
	public $content;
	public $title;
	private $position = 0;
	
	function setformfields($formfields) {
		foreach($formfields as $formfield) {
			$this->_add_formfield($formfield); // Add the formfield
		}
		return;
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
		
		$name 	= $formfield['name'];
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
					// 'place'			=> $place, We don't do anything with this, do we?
				);
			break;
			
			case 'textfield': // if type is textfield, add it to textfields
				$this->textfields[$name] = array(
					'name' 			=> $name,
					'title'			=> $title,
					'value'			=> $value,
					'description'	=> $desc,
					// 'place'			=> $place, We don't do anything with this, do we?
				);
			break;
			
			case 'selectbox':
				$this->selectboxes[$name] = array(
					'name'			=> $name,
					'title'			=> $title,
					'description'	=> $desc,
					'values'		=> $value
				);
			break;

			case 'radio':
				$this->radios[$name] = array(
					'name'			=> $name,
					'title'			=> $title,
					'description'	=> $desc,
					'values'		=> $value,
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
	
	function contactform() {
		// Add subject
		$this->_add_formfield(array(
			'type'			=> 'textfield',
			'name'			=> 'subject',
			'title'			=> __('Subject'),
			'description'	=> __('Give the subject.'),
			'place'			=> 1,
		));
		// Add from
		$this->_add_formfield(array(
			'type'			=> 'textfield',
			'name'			=> 'from',
			'title'			=> __('Your email address:'),
			'description'	=> __('Give your email address. This will be used to answer your message.'),
			'place'			=> 2,
		));
		// Add message textarea
		$this->_add_formfield(array(
			'type'			=> 'textarea',
			'name'			=> 'message',
			'title'			=> __('Message'),
			'description'	=> __('Give the message to send.'),
			'place'			=> 3,	
		));
		// Add hidden standardform - requires no position
		$this->_add_formfield(array(
			'type'	=> 'hidden',
			'name'	=> 'contactform',
		));
		// Submit
		$this->submit = __('Send');	
		// Add action, because of strange webkit...
		$this->action = $_SERVER['REQUEST_URI'];		
		return;		
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
		<input type="submit" value="$this->submit" /> 
	</fieldset>
</form>					
CONTENT;
	}
	
	function _generate_textfield($textfield_name) {
		$textfield 		= $this->textfields[$textfield_name];
		$name	 		= $textfield['name'];
		$title			= $textfield['title'];
		$description	= $textfield['description'];
		$value			= $textfield['value'];
		
		$content = <<<CONTENT
		<strong>$title</strong><br />
		<input type="text" name="$name" value="$value" /><br />
		<span class="small">$description</span><br ><br />
CONTENT;
	$this->content .= $content;
	return;		
	}
	
	function _generate_textarea($textarea_name) {
		$textarea 		= $this->textareas[$textarea_name];
		$name	 		= $textarea['name'];
		$title			= $textarea['title'];
		$description	= $textarea['description'];
		$value			= $textarea['value'];
		
		$content = <<<CONTENT
		<strong>$title</strong><br />
		<textarea cols="30" rows="4" name="$name">$value</textarea><br />
		<span class="small">$description</span><br ><br />
CONTENT;
	$this->content .= $content;
	return;		
	}
	
	function _generate_radio($name) {
		$item = $this->radios[$name];
		
		$inputs = '';
		foreach ($item['values'] as $key => $value) {
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
		
		$this->content .= <<<CONTENT
		<strong>{$item['title']}</strong><br />
		{$inputs}<br />		
		<span class="small">{$item['description']}</span><br ><br />
CONTENT;
	}
	
	function _generate_selectbox($name) {
		$item = $this->selectboxes[$name];
		
		$selectbox = '<select name="' . $item['name'] . '">';
		foreach ($item['values'] as $key => $value) {
			$selected = ($value['selected']) ? ' selected="selected"' : '';
			$selectbox .= '<option value="' . $key . '"' . $selected . '>' . $value['title'] . '</option>';
		}
		$selectbox .= '</select>';
		
		$this->content .= <<<CONTENT
		<strong>{$item['title']}</strong><br />
		{$selectbox}<br />
		<span class="small">{$item['description']}</span><br ><br />
CONTENT;
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

	function extinfo() {
		return array(
			'version'		=> '0.0.1',
			'name'			=> __('Form extension'),
			'description'	=> __('This extension provides a form API to other extensions.'),
		);
	}
}
?>