<?php
define('IN_VIENNACMS', true);
include('../start.php');
include(ROOT_PATH . 'includes/form.php');

class testform extends form {
	function __construct() {
		$this->elements = array(
			__('Page caching') => array(
				'caching_type' => array(
					'type'			=> 'radio',
					'name'			=> 'caching_type',
					'title'			=> __('Caching type'),
					'description'	=> __('The \'simple\' cache option is the most safe option, and is currently the recommended one. The \'normal\' option will cache all page output, except for pages with <em>dynamic modules</em>. Dynamic blocks will still be cached, so minor side-effects may occur. The \'aggressive\' cache option will cache every page in the front end, until it expires or is modified.'),
					'value'			=> array(
						'simple' => array(
							'title' => 'Simple (most safe)',
							'selected' => ($config['caching_type'] == 'simple' || empty($config['caching_type']))
						),
						'normal' => array(
							'title' => 'Normal (minor side-effects with blocks)',
							'selected' => ($config['caching_type'] == 'normal')
						),
						'aggressive' => array(
							'title' => 'Aggressive (can cause problems)',
							'selected' => ($config['caching_type'] == 'aggressive')
						)
					)
				),
				'caching_time' => array(
					'type'			=> 'selectbox',
					'name'			=> 'caching_time',
					'title'			=> __('Output cache time'),
					'description'	=> __('The time the cache keeps existing if there are no changes.'),
					'value'			=> utils::get_hours($config['caching_time'])
				),
				'banana' => array(
					'type'			=> 'textfield',
					'name'			=> 'banana',
					'title'			=> 'bananas',
					'description'	=> 'like it?',
					'value'			=> '',
					'required'		=> true,
					'max_length'	=> 4
				)
			)
		);
		$this->form_id = 'hoi2000';
		$this->action = '';
	}
	
	function validate($field, $value) {
		if ($field == 'caching_type') {
			if ($value == 'aggressive') {
				return 'Aggresieve caching werkt niet, denk ik';
			}
		}
	}
}
?> 
<link rel="stylesheet" href="../extensions/core/form.css" />
<?php
$form = new testform;
$api = new formapi;
echo $api->get_form($form);
?>