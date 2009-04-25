<?php
class extension_captcha {
	public function __construct() {
		VEvents::register('captcha.get-implementations', array($this, 'captcha_implementations'));
		VEvents::register('captcha.secured-forms', array($this, 'captcha_secured_forms'));
		VEvents::register('acp.get-system-items', array($this, 'acp_system_pane'));
		VEvents::register('form.alter', array($this, 'form_alter'));
	}
	
	function acp_system_pane() {
		return array(
			'captcha' => array(
				'title' => __('CAPTCHA settings'),
				'icon' => '~/extensions/captcha/settings.png',
				'href' => 'admin/controller/captcha/settings'
			)
		);
	}
	
	function captcha_implementations() {
		return array(
			'math_captcha' => array(
				'title' => __('Math CAPTCHA'),
				'extension' => 'captcha'
			)
		);
	}
	
	function captcha_secured_forms() {
		return array(
			'captcha_settings' => true
		);
	}
	
	function form_alter($form_id, $params) {
		//$secured_forms = manager::run_hook_all('captcha_secured_forms');
		$secured_forms = VEvents::invoke('captcha.secured-forms');
		
		if (!isset($secured_forms[$form_id]) || !$secured_forms[$form_id]) {
			return;
		}
		
		// okay, the problem is up to us now.
		$extension = $this->get_current_extension();
		
		if ($extension == false) {
			return;
		}
		
		$name = cms::$config['captcha_implementation'];
		
		$generate_callback = $name . '_generate';
		$result = $extension->$generate_callback();
		
		$answer = $result['answer'];
		
		$params->data['fields']['captcha_data'] = $result['form_field'];
		$params->data['fields']['captcha_data']['group'] = 'captcha';
		$params->data['fields']['captcha_data']['required'] = true;
		$params->data['fields']['captcha_data']['weight'] = 800;
		$params->data['groups']['captcha'] = array(
			'title' => __('Verification'),
			'expanded' => true
		);

		$captcha_id = sha1(uniqid(time()));
		
		$params->data['fields']['captcha_id'] = array(
			'type' => 'hidden',
			'value' => $captcha_id,
			'group' => 'captcha',
			'weight' => 800,
			'refresh' => true
		);
		
		$params->data['fields']['captcha_solution'] = array(
			'type' => 'value',
			'value' => $answer,
			'group' => 'captcha',
			'weight' => 800
		);
		
		$params->data['options']['render_hooks'][] = array($this, 'form_render');
		$params->data['options']['validate_hooks'][] = array($this, 'form_validate');
	}
	
	public function form_validate($fields, &$errors) {
		$captcha_data = unserialize(cms::$config['captcha_' . $fields['captcha_id']]);
		
		if ($captcha_data['answer'] != $fields['captcha_data']) {
			$errors['captcha_data'] = __('The entered solution to the verification question is incorrect.');
			
			cms::log('captcha', 'The CAPTCHA verification code was entered incorrectly on a form.', 'warn');
		}
		
		unset(cms::$config['captcha_' . $fields['captcha_id']]);
	}
	
	public function form_render($form_data) {
		cms::$config['captcha_' . $form_data['fields']['captcha_id']['value']] = serialize(array(
			'time' => time(),
			'answer' => $form_data['fields']['captcha_solution']['value']
		));
	}
	
	public function math_captcha_generate() {
		$result = array();
		$answer = mt_rand(1, 20);
        $x = mt_rand(1, $answer);
        $y = $answer - $x;
        
        $result['answer'] = $answer;
        $result['form_field'] = array(
        	'type' => 'textbox',
        	'label' => __('Math question'),
        	'description' => __('Solve this simple math question, and enter the result below. For example, for 1 + 3, enter 4.'),
        	'field_prefix' => sprintf('%d + %d = ', $x, $y),
        );
		
		return $result;
	} 
	
	private function get_current_extension() {
		//$implementations = manager::run_hook_all('captcha_implementations');
		$implementations = VEvents::invoke('captcha.get-implementations');
		$ext_name = $implementations[(string)cms::$config['captcha_implementation']]['extension'];
		
		if (empty($ext_name)) {
			return false;
		}
		
		return manager::load_extension($ext_name);
	}
}