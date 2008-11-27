<?php
class HTMLContentController extends Controller {
	public function run() {
		return array(
			'title' => $this->arguments['title'],
			'content' => $this->arguments['content']
		);
	}
	
	public function friendlyname() {
		return __('HTML content');
	}
	
	public function arguments($module) {
		return array(
			'fields' => array(
				'title' => array(
					'label' => __('Title'),
					'description' => __('The title of this module.'),
					'required' => true,
					'type' => 'textbox',
					'value' => $module['arguments']['title'],
					'group' => 'all',
					'weight' => -10
					),
				'content' => array(
					'label' => __('Content'),
					'description' => __(''),
					'required' => true,
					'type' => 'wysiwyg',
					'value' => $module['arguments']['content'],
					'group' => 'all',
					'weight' => -10
					)
				),
			'groups' => array(
				'all' => array(
					'title' => __('Module'),
					'expanded' => true
					)
				)
		);
	}
}
?>