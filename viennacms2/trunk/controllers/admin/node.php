<?php
class AdminNodeController {
	public function add() {
		$parent = new Node();
		$parent->node_id = $this->arguments[0];
		$parent->read(true);
		if (empty($parent->title)) {
			echo '<ul class="submenu">' . __('Please select a node before adding a sub node.') . '</ul>';
			exit;
		}
		?>
		<ul class="types submenu">
		<?php
		$types = manager::run_hook_all('get_node_types');
		foreach ($types as $key => $type) {
			$node = new Node();
			$node->type = $key;
			
			if (!cms::display_allowed('this_under_other', $node, $parent)) {
				continue;
			}
			
			?>
			<li>
			<a class="<?php echo $key ?>" href="<?php echo $this->view->url('admin/controller/node/add_do/' . $key . '/' . $parent->node_id); ?>"><?php echo $type['title'] ?></a><span><?php echo $type['description'] ?></span>
			</li>
			<?php
		}
		?>
		</ul>
		<?php
		exit;
	}
	
	public function add_do() {
		$this->edit('add');
	}
	
	public function edit($do = 'edit') {
		if ($do == 'edit') {
			$node = new Node();
			$node->node_id = $this->arguments[0];
			$node->read(true);
		
			if (empty($node->title)) {
				trigger_error(__('This node does not exist!'));
			}
		} else {
			$node = Node::create('Node');
			$node->type = $this->arguments[0];
			$node->parent = $this->arguments[1];
			$node->set_type_vars();
			
			$ux_html = '<li class="oncontentremove"><a class="' . $node->type . ' mynewnode" href="#">' . __('New node') . '</a></li>';
			?>
			<script type="text/javascript">
				hasChildren = $('.treeview a.selected').parents('li').eq(0).find('ul').length;
				if (hasChildren > 0) {
					var add = $('.treeview a.selected').parents('li').eq(0).find('ul').eq(0).append('<?php echo $ux_html ?>');
					$('.treeview').treeview({ add: add });
				} else {
					var add = $('.treeview a.selected').parents('li').eq(0).append('<ul class="oncontentremove open"><?php echo $ux_html?></ul>')
					$('.treeview').treeview({ add: add });
				}
				
				$('.treeview a.selected').removeClass('selected');
				$('.mynewnode').addClass('selected');
				
				$('#node_edit_title').keyup(function() {
					value = $(this).val();
					if (value == "") {
						$('.mynewnode').html('<?php echo __('New node') ?>');
					} else {
						$('.mynewnode').html(value);
					}
				});
			</script>
			<?php
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
		
		if ($node->typedata['type'] == 'dynamic') {
			$form_data['fields']['revision_content'] = array(
				'label' => __('Content'),
				'description' => __(''),
				'required' => true,
				'type' => 'html',
				'value' => $this->module_editor($node),
				'group' => 'node_revision',
				'weight' => 5
			);
			
			$form_data['groups']['node_revision'] = array(
				'title' => __('Modules'),
				'expanded' => true
			);
		}
		
		if ($do == 'edit') {
			$form_data['fields']['node_id'] = array(
				'type' => 'hidden',
				'required' => true,
				'value' => $node->node_id,
				'group' => 'node_details',
				'weight' => -10
 			);
		} else {
			$form_data['fields']['type'] = array(
				'type' => 'hidden',
				'required' => true,
				'value' => $node->type,
				'group' => 'node_details',
				'weight' => -10
			);
			
			$form_data['fields']['parent'] = array(
				'type' => 'hidden',
				'required' => true,
				'value' => $node->parent,
				'group' => 'node_details',
				'weight' => -10
			);
		}
		
		$form_data['fields']['do'] = array(
			'type' => 'hidden',
			'required' => true,
			'value' => $do,
			'group' => 'node_details',
			'weight' => -10
		);
		
		$form = new Form();
		$form->callback_object = $this;
		echo $form->handle_form('node_edit', $form_data);
		exit;
	}
	
	public function edit_module() {
		$node = new Node();
		$node->node_id = $this->arguments[0];
		$node->read(true);
		if (!isset($_POST['node_edit_revision_content'])) {
			$rmodules = unserialize($node->revision->content);
		} else {
			$rmodules = unserialize(base64_decode($_POST['node_edit_revision_content']));
		}
		
		foreach ($rmodules[$this->arguments[1]] as &$tmodule) {
			if ($tmodule['id'] == $this->arguments[2]) {
				$mm = &$tmodule;
				break;
			}
		}
		
		if (!$mm) {
			if (isset($this->arguments[3])) {
				$mo = 0;
				foreach ($rmodules[$this->arguments[1]] as $module) {
					if ($module['order'] > $mo) {
						$mo = $module['order'];
					}
				}
				
				$module = array(
					'controller' => $this->arguments[3],
					'order' => $mo + 1,
					'arguments' => array(
					),
					'id' => $this->arguments[2]
				);
				
				$rmodules[$this->arguments[1]][] = &$module;
				$mm = &$module;
			} else {
				exit;
			}
		}
		
		$module = &$mm;
		$controller = cms::$manager->get_controller($module['controller']);
		$data = $controller->arguments($module);

		if (!empty($_POST)) {
			$haschanged = false;
			
			foreach ($_POST as $key => $value) {
				if (strpos($key, 'modulee_') === 0) {
					$haschanged = true;
					$module['arguments'][str_replace('modulee_', '', $key)] = $value;
				}
			}
			
			if ($haschanged) {
				echo base64_encode(serialize($rmodules));
				exit;
			}
		}
		?>
		<div class="modform" style="display: none;">
		<?php
		$form = new Form();
		$form->return = true;
		echo $form->handle_form('modulee', $data);
		?>
		</div>
		<?php
		exit;
	}
	
	private function module_editor($node) {
		ob_start();
		?>
		<ul class="modules" id="modules-content">
			<?php
			$rmodules = unserialize($node->revision->content);
			if ($rmodules == false) {
				$rmodules = array('content' => array(
					array(
						'controller' => 'htmlcontent',
						'order' => 0,
						'arguments' => array(
							'title' => __('No content'),
							'content' => __('This module does not have any content.')
						),
						'id' => md5(uniqid(time()))
					)
				));
			}
			$modules = array();
		
			foreach ($rmodules['content'] as $module) {
				$modules[$module['order']] = $module;
			}
			
			ksort($modules);
			
			foreach ($modules as $module) {
				$controller = cms::$manager->get_controller($module['controller']);
				$name = $controller->friendlyname();
				echo '<li class="module-' . $module['controller'] . '" id="module-' . $module['id'] . '"><a href="' . $this->view->url('admin/controller/node/edit_module/' . $node->node_id . '/content/' . $module['id']) . '">';
				echo $name;
				echo '</a></li>'; 
			}
			?>
			<li class="module-add" id="module-XX"><a href="<?php echo $this->view->url('admin/controller/node/new_module/' . $node->node_id . '/content') ?>">
			<?php echo __('Add'); ?>
			</a></li>
		</ul>
		<input type="hidden" name="node_edit_revision_content" id="nerc" value='<?php echo base64_encode(serialize($rmodules)) ?>' />
		<?php
		$c = ob_get_contents();
		ob_end_clean();
		
		return $c;
	}
	
	public function new_module() {
		if (empty($this->arguments[2])) {
			?>
			<div class="modform">
			<ul class="types submenu" style="display: block;">
			<?php
			$types = manager::run_hook_all('module_manifest');
			foreach ($types as $key => $type) {
				?>
				<li>
				<a style="background-image: url(<?php echo str_replace('~/', manager::base(), $type['icon']) ?>); background-repeat: no-repeat;" href="<?php echo $this->view->url('admin/controller/node/new_module/' . $this->arguments[0] . '/content/' . $key); ?>"><?php echo $type['title'] ?></a><span><?php echo $type['description'] ?></span>
				</li>
				<?php
			}
			?>
			</ul>
			</div>
			<?php
			exit;
		}
		
		$node = new Node();
		$node->node_id = $this->arguments[0];
		$node->read(true);
		$mo = 0;
		$rmodules = unserialize(base64_decode($_POST['nerc']));
		foreach ($rmodules[$this->arguments[1]] as $module) {
			if ($module['order'] > $mo) {
				$mo = $module['order'];
			}
		}
		
		$module = array(
			'controller' => $this->arguments[2],
			'order' => $mo + 1,
			'arguments' => array(
			),
			'id' => md5(uniqid(time()))
		);
		$rmodules[$this->arguments[1]][] = $module;
		$string = base64_encode(serialize($rmodules));
		$controller = cms::$manager->get_controller($module['controller']);
		$name = $controller->friendlyname();
		echo '<li class="module-' . $module['controller'] . '" id="module-' . $module['id'] . '"><a href="' . $this->view->url('admin/controller/node/edit_module/' . $node->node_id . '/content/' . $module['id'] . '/' . $module['controller']) . '">';
		echo $name;
		echo '</a></li>'; 
		?>
		
		<script type="text/javascript">
			$('#nerc').attr('value', '<?php echo $string ?>');
		</script>
		<?php
		exit;
	}
	
	public function node_edit_submit($fields) {
		switch ($fields['do']) {
			case 'edit':
				$node = new Node();
				$node->node_id = $fields['node_id'];
				$node->read(true);
				
				if (empty($node->title)) {
					trigger_error(__('This node does not exist!'));
				}
				break;
			case 'add':
				$node = Node::create('Node');
				$node->type = $fields['type'];
				$node->parent = $fields['parent'];
				$node->set_type_vars();
				break;
		}
		
		$node->title = $fields['title'];
		$node->description = $fields['description'];
		
		// set options
		if (!empty($node->typedata['options'])) {
			foreach ($node->typedata['options'] as $id => $option) {
				$aid = 'option_' . $id;
				if (isset($fields[$aid])) {
					$node->options[$id] = $fields[$aid];
				}
			}
		}
		
		// revision?
		if ($node->typedata['type'] == 'static') {
			$node->revision->content = $fields['revision_content'];
		} else if ($node->typedata['type'] == 'dynamic') {
			$node->revision->content = base64_decode($fields['revision_content']);
		}
		
		// write it!
		$node->write();
		
		echo __('The node has been successfully saved.');
		?>
		<script type="text/javascript">
			load_active_panes();
		</script>
		<?php
	}
}
