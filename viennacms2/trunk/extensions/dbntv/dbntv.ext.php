<?php
class extension_dbntv {
	public function get_node_types() {
		return array(
			'guide' => array(
				'extension' => 'dbntv',
				'title' => __('TV guide'),
				'description' => __('A dbn.tv guide page/preset.'),
				'type' => 'none',
				'icon' => '~/blueprint/views/admin/images/icons/page.png',
				'options' => array(
					'params' => array(
						'label' => __('Parameters'),
						'description' => __('Any URL parameters for dbn.tv.'),
						'type' => 'textbox',
						'required' => false
					)
				)
			),
		);
	}
	
	function parse_args($arguments) {
		if (!is_array($arguments)) {
			$arguments = explode('/', $arguments);
		}
		
		$return = array();
		
		foreach ($arguments as $argument) {
			$temp = explode('=', $argument);
			
			$return[$temp[0]] = $temp[1];
		}
		
		return $return;
	}
}