<?php
class AdminCAPTCHAController extends Controller {
	public function settings() {
		$form_data = array(
			'fields' => array(
				'module' => array(
					'label' => __('CAPTCHA implementation'),
					'description' => __('The implementation to use for displaying the CAPTCHA. It\'s usually safe to leave this at the default value.'),
					'required' => true,
					'type' => 'select',
					'value' => cms::$config['captcha_implementation'],
					'group' => 'core_settings',
					'weight' => 0,
					'values' => $this->get_captcha_types()
				)
			),
			'groups' => array(
				'core_settings' => array(
					'title' => __('Main settings'),
					'expanded' => true
				)
			)
		);
		
		$form = new Form();
		$form->callback_object = $this;
		
		$output = $form->handle_form('captcha_settings', $form_data);
		
		return $output;
	}
	
	public function captcha_settings_submit($data) {
		cms::$config['captcha_implementation'] = $data['module'];
				
		return __('CAPTCHA settings are saved.');
	}
	
	public function get_captcha_types() {
		$hooks = manager::run_hook_all('captcha_implementations');
		$data = array();
		
		foreach ($hooks as $key => $hook) {
			$data[$hook['title']] = $key;
		}
		
		return $data;
	}
}