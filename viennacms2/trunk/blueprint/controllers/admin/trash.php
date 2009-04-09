<?php
class AdminTrashController extends Controller {
	public function can() {
		$node = new Node();
		$node->node_id = $this->arguments[0];
		$node->read(true);
		
		ob_start();
		
		// not implemented yet
		
		$contents = ob_get_contents();
		ob_end_clean();
		
		admincontroller::set_context('node', $node);
		
		return $contents;
	}
	
	public function delete() {
		$node = new Node();
		$node->node_id = $this->arguments[0];
		$node->read(true);
		$node->options['trash_old_parent'] = $node->parent;
		$node->parent = cms::$helpers->trashroot->node_id;
		$node->write();
		
		admincontroller::notify(sprintf(__('The node %s has been put into the trash can.'), $node->title));
		
		cms::redirect($node->to_admin_url());
	}
	
	public function clear() {
		$can = new Node();
		$can->node_id = $this->arguments[0];
		$can->read(true);
		
		if ($can->type != 'trashcan') {
			return __('That\'s not a trash can!');
		}
		
		admincontroller::set_context('node', $can);
		
		$form_data = array(
			'fields' => array(
				'really' => array(
					'type' => 'html',
					'value' => __('Do you really want to empty the trash can? The content of the can will be removed without any chance of getting the data back.'),
					'group' => 'confirmation'
				)
			),
			'groups' => array(
				'confirmation' => array(
					'title' => __('Confirm'),
					'expanded' => true
				)
			)
		);
		
		$form = new Form();
		$form->callback_object = $this;
		return $form->handle_form('empty_trash', $form_data);
	}
	
	public function empty_trash_submit($data) {
		$can = new Node();
		$can->node_id = $this->arguments[0];
		$can->read(true);
		
		cms::$helpers->remove_node_children($can);
		
		admincontroller::notify(__('The trash can has been successfully emptied.'));
		cms::redirect('admin/controller/trash/can/' . $can->node_id);
	}
	
	public function restore() {
		$node = new Node();
		$node->node_id = $this->arguments[0];
		$node->read(true);
		$parent = (string)$node->options['trash_old_parent'];
		unset($node->options['trash_old_parent']);
		$node->parent = $parent;
		$node->write();
		
		admincontroller::notify(sprintf(__('The node %s has been restored from the trash can.'), $node->title));
		cms::redirect($node->to_admin_url());
	}
}