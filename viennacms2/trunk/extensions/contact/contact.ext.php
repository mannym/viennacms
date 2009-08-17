<?php
class extension_contact {
	public function __construct() {
		VEvents::register('captcha.secured-forms', array($this, 'captcha_secured_forms'));
		Registry::register_type('ContactNode');
	}
	
	public function get_node_types() {
		return array(
			'contact' => array(
				'extension' => 'contact',
				'title' => __('Contact form'),
				'description' => __('A simple contact form allowing users to email you via the website.'),
				'type' => 'none',
				'icon' => '~/extensions/contact/message.png',
				'options' => array(
					'email' => array(
						'label' => __('E-mail address'),
						'description' => __('The e-mail address on which the mail will be delivered.'),
						'type' => 'textbox',
						'required' => true,
						'validate_function' => array($this, 'validate_email')
					)
				),
				'display_callback' => array($this, 'contact'),
			),
		);
	}
	
	public function captcha_secured_forms() {
		return array(
			'contact_form' => true
		);
	}

}