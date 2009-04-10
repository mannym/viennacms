<?php
class extension_contact {
	public function get_node_types() {
		return array(
			'contact' => array(
				'extension' => 'contact',
				'title' => __('Contact form'),
				'description' => __('A simple contact form allowing users to email you via the website'),
				'type' => 'none',
				'icon' => '~/extensions/contact/message.png',
				'options' => array(
					'email' => array(
						'label' => __('E-mail address'),
						'description' => __('The e-mail address on which the mail will be delivered'),
						'type' => 'textbox',
						'required' => true,
					)
				),
				'display_callback' => array($this, 'contact'),
			),
		);
	}
	
	public function contact($node, $arguments)
	{
		$emailaddress = (string) $node->options['email'];
		
	}
}