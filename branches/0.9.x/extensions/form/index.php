<?php
/**
* Form extension for the viennaCMS
* 
* @package viennaCMS
* @author viennainfo.nl
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*/

if (!defined('IN_VIENNACMS')) {
	exit;
}

class extension_form {
	public $textareas = array();
	public $textfields = array();
	public $hiddenfields = array();
	public $place = array();
	public $submit;
	public $action;
	public $content;
	
	function setformfields($formfields) {
		foreach($formfields as $formfield) {
			$this->_add_formfield($formfield); // Add the formfield
		}
		return;
	}
	
	private function _add_formfield($formfield) {
		$type 	= $formfield['type'];
		$name 	= $formfield['name'];
		$value	= isset($formfield['value']) ? $formfield['value'] : ''; // Not required
		$title	= isset($formfield['title']) ? $formfield['title'] : ''; // Not required for hidden formfields
		$desc	= isset($formfield['description']) ? $formfield['description'] : ''; // Not required for hidden formfields
		/**
		 * TODO: Automaticaly generate the place of the field
		 */
		$place	= isset($formfield['place']) ? $formfield['place'] : '';
		
		if(!empty($place)) {
			switch ($type) {
				case 'textarea': // Set the place of the formfield. Not for hidden or submit:
				case 'textfield':
					$this->place[$place]['name'] = $name;
					$this->place[$place]['type'] = $type;					
				break;
			}
		}
		
		switch($type) {
			case 'textarea': // if type is textarea, add it to textareas
				$this->textareas[$name] = array(
					'name' 			=> $name,
					'title'			=> $title,
					'value'			=> $value,
					'description'	=> $desc,
					'place'			=> $place,
				);
			break;
			
			case 'textfield': // if type is textfield, add it to textfields
				$this->textfields[$name] = array(
					'name' 			=> $name,
					'title'			=> $title,
					'value'			=> $value,
					'description'	=> $desc,
					'place'			=> $place,
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
		return;		
	}
	
	function generateform() {
		$this->content = <<<CONTENT
<form action="$this->action" method="post">
	<table width="100%">
CONTENT;
		
		foreach ($this->place as $place) {
			switch ($place['type']) {
				case 'textarea':
					$this->_generate_textarea($place['name']);
				break;
				case 'textfield':
					$this->_generate_textfield($place['name']);
				break;
			}
		}
		
		foreach ($this->hiddenfields as $field) {
			$this->_generate_hidden_formfield($field['name']);
		}
		
		$this->content .= <<<CONTENT
		<tr>
			<td colspan="2">
				<input type="submit" value="$this->submit" /> 
			</td>
		</tr>
	</table>
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
		<tr>
			<td width="70%">
				<strong>$title</strong><br />
				$description
			</td>
			<td width="30%">
				<input type="text" name="$name" value="$value" />
			</td>
		</tr>					
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
		<tr>
			<td width="45%">
				<strong>$title</strong><br />
				$description
			</td>
			<td width="55%">
				<textarea cols="30" rows="4" name="$name">$value</textarea>
			</td>
		</tr>					
CONTENT;
	$this->content .= $content;
	return;		
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