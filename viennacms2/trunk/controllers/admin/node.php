<?php
class AdminNodeController {
	public function edit() {
		if (!empty($this->arguments[0])) {
			$do = 'edit';
			
			$node = new Node();
			$node->node_id = $this->arguments[0];
			$node->read(true);
		
			if (empty($node->title)) {
				trigger_error(__('This node does not exist!'));
			}
		} else {
			$node = Node::create('Node');
		}
		
		$form_data = array(
			'fields' => array(
				'title' => array(
					'label' => __('Title'),
					'description' => __('The title of this node, which will be displayed for example in the menu.'),
					'required' => true,
					'type' => 'textbox',
					'value' => $node->title,
					'group' => 'node_details',
					'weight' => -10
				),
				'description' => array(
					'label' => __('Description'),
					'description' => __('The description of this node, which you can see in themes and modules that support this feature.'),
					'required' => false,
					'type' => 'textarea',
					'value' => $node->description,
					'group' => 'node_details',
					'weight' => -10
				)
			),
			'groups' => array(
				'node_details' => array(
					'title' => __('Node details'),
					'expanded' => true
				)
			)
		);
		
		$options = array();
		
		if (!empty($node->typedata['options'])) {
			foreach ($node->typedata['options'] as $id => $option) {
				$aid = 'option_' . $id;
				$form_data['fields'][$aid] = $option;
				$form_data['fields'][$aid]['group'] = 'node_options';
				$form_data['fields'][$aid]['value'] = $node->options[$id];
				$form_data['fields'][$aid]['weight'] = 0;
			}
			
			$form_data['groups']['node_options'] = array(
				'title' => __('Node options'),
				'expanded' => false
			);
		}
		
		if ($node->typedata['type'] == 'static') {
			$form_data['fields']['revision_content'] = array(
				'label' => __('Content'),
				'description' => __(''),
				'required' => true,
				'type' => 'wysiwyg',
				'value' => $node->revision->content,
				'group' => 'node_revision',
				'weight' => 5
			);
			
			$form_data['groups']['node_revision'] = array(
				'title' => __('Content'),
				'expanded' => true
			);
		}
		
		$form = new Form();
		$form->handle_form('node_edit', $form_data);
		exit;
	}
}
