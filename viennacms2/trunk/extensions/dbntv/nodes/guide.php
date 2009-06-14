<?php
class GuideNode extends Node {
	public $has_modules = false;
	public $has_revision = false;
	public $is_legacy = false;
	
	public function display($arguments) {
		if (empty($arguments)) {
			$test = (string) $this->options['params'];
			
			if (empty($test)) {
				$test = 'start=gt,' . time() . '/view=raw/end=lt,' . (time() + 3600);
			}
			
			$arguments = cms::ext('dbntv')->parse_args($test);
		} else {
			$arguments = cms::ext('dbntv')->parse_args($arguments);
		}
		
		
	}
	
	public function get_typedata() {
		return array(
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
			);
	}
}