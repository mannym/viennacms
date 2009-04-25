<?php
class extension_contact {
	public function __construct() {
		VEvents::register('captcha.secured-forms', array($this, 'captcha_secured_forms'));
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
	
	private $page_node;
	
	public function contact($node, $arguments)
	{
		$this->page_node = $node;
		
		$email = (string) $node->options['email'];
		
		$form_data = array(
			'fields' => array(
				'from_name' => array(
					'label' => __('Your name'),
					'description' => __('Enter your name here.'),
					'required' => true,
					'type' => 'textbox',
					'group' => 'contact',
					'weight' => -10
				),
				'from_email' => array(
					'label' => __('Your e-mail address'),
					'description' => __('Enter your e-mail address in this box.'),
					'required' => true,
					'type' => 'textbox',
					'group' => 'contact',
					'weight' => -10,
					'validate_function' => array($this, 'validate_email')
				),
				'subject' => array(
					'label' => __('Subject'),
					'description' => __('Enter the e-mail subject in this box.'),
					'required' => true,
					'type' => 'textbox',
					'group' => 'contact',
					'weight' => -10,
				),
				'message' => array(
					'label' => __('Message'),
					'description' => __('Enter the message you want to send.'),
					'required' => true,
					'type' => 'textarea',
					'group' => 'contact',
					'attributes' => 'style="width: 500px; height: 200px;"',
					'weight' => -10,
				)
			),
			'groups' => array(
				'contact' => array(
					'title' => __('Contact'),
					'expanded' => true
				)
			)
		);
		
		$form = new Form();
		$form->callback_object = $this;
		return $form->handle_form('contact_form', $form_data);
	}
	
	public function contact_form_submit($fields) {
		$email = (string) $this->page_node->options['email'];
		
		$from_name = str_replace(array("\r", "\n"), '', $fields['from_name']);
		$from_email = str_replace(array("\r", "\n"), '', $fields['from_email']);
		$subject = str_replace(array("\r", "\n"), '', $fields['subject']);
		$message = $fields['message'];
		
		// TODO: improve this
		$send_msg = sprintf("%s\nMessage sender: %s <%s>\nUser IP: %s", $message, $from_name, $from_email, $_SERVER['REMOTE_ADDR']);
		
		mail($email, $subject, $send_msg, 'From: ' . $from_email);
		
		return __('The e-mail has been sent.');
	}
	
	public function validate_email($email) {
		if (!preg_match("/^[a-z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/i", $email)) {
			return __('This e-mail address is not correct.');
		}
	}
}