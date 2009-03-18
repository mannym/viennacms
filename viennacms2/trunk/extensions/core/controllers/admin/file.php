<?php
class AdminFileController extends Controller {
	public function folder() {
		$file = new Node();
		$file->node_id = $this->arguments[0];
		$file->read(true);

		$this->view->path = 'admin/simple.php';

		$prefix = '';

		if ($file->node_id) {
			$toolbars = manager::run_hook_all('node_toolbar', $file);

			$prefix = AdminController::add_toolbar($toolbars, $this);
		}

		$this->view['data'] = $prefix;
	}
	
	public function file() {
		$file = new Node();
		$file->node_id = $this->arguments[0];
		$file->read(true);
		
		$this->view['preview'] = cms::$files->get_file_widget($file);
		$this->view['file'] = $file;
		
		$this->view['delete_url'] = $this->view->url('admin/controller/file/delete/file/' . $this->arguments[0]);

		$prefix = '';

		if ($file->node_id) {
			$toolbars = manager::run_hook_all('node_toolbar', $file);

			$prefix = AdminController::add_toolbar($toolbars, $this);
		}

		$this->view['prefix'] = $prefix;
	}
	
	public function delete() {
		if ($this->arguments[0] == 'file') {
			$this->view->path = 'admin/simple.php';
			
			$file = new Node();
			$file->node_id = $this->arguments[1];
			$file->read(true);
			
			if (!$file->title) {
				$this->view['data'] = __('The file does not exist!');
				return;
			}
			 			
			unlink(ROOT_PATH . $file->description);
			$file->delete();
			
			// it was a fun test, and we're all impressed
			$this->view['data'] = __('The file has been successfully deleted.');
		}
	}
	
	public function upload() {
		$this->view->path = 'admin/simple.php';
		
		$max_size = cms::$helpers->return_bytes(ini_get('upload_max_filesize')) / 1024 / 1024;
		
		$parent = new Node();
		$parent->node_id = $this->arguments[0];
		$parent->read(true);
		
		if (!$parent->title) {
			$this->view['data'] = __('The parent node does not exist.');
			return;
		}
		
		$form_data = array(
			'fields' => array(
				'file' => array(
					'label' => __('File'),
					'description' => sprintf(__('The file to upload to the site. Maximum size: %s MB'), $max_size),
					'required' => true,
					'type' => 'file',
					'group' => 'file',
					'weight' => 0,
					'validate_function' => array($this, 'validate_file')
				),
				'parent_id' => array(
					'type' => 'hidden',
					'required' => true,
					'value' => $parent->node_id,
					'group' => 'file',
					'validate_function' => array(cms::$helpers, 'validate_node')
				)
			),
			'groups' => array(
				'file' => array(
					'title' => __('File data'),
					'expanded' => true
				)
			)
		);
		
		$form = new Form();
		$form->callback_object = $this;
		$form->form_attributes .= ' enctype="multipart/form-data"';
		$this->view['data'] = $form->handle_form('file_upload', $form_data);
	}
	
	public function file_upload_submit($data) {
		$target = 'files/' . md5(uniqid(time())) . '.upload';
		
		move_uploaded_file($data['file']['tmp_name'], ROOT_PATH . $target);
		
		$node = Node::create('Node');
		$node->parent = $data['parent_id'];
		$node->title = $data['file']['name'];
		$node->description = $target;
		$node->type = 'file';
		$node->set_type_vars();
		$node->created = time();
		$node->options['mimetype'] = $data['file']['type'];
		$node->options['size'] = filesize($target);
		$node->options['downloads'] = 0;
		$node->write();
		
		cms::$helpers->create_node_alias($node);
		
		return __('The file has successfully been uploaded.');
	}
	
	public function editor_widget() {
		$file = new Node();
		$file->node_id = $this->arguments[0];
		$file->read(true);
		
		$output = '<viennacms:file node="' . $file->node_id . '">';
		$output .= cms::$files->get_file_widget($file);
		$output .= '</viennacms:file>&nbsp;';
		
		echo $output;
		exit;
	}
	
	public function validate_file($file) {
		if (!is_uploaded_file($file['tmp_name'])) {
			return __('The file is invalid.');
		}

		if ($file['error'] != UPLOAD_ERR_OK) {
			$error = $file['error'];
			
			switch ($error) {
				case UPLOAD_ERR_INI_SIZE:
					return __('The file exceeds the maximum file size.');
				break;
				case UPLOAD_ERR_PARTIAL:
					return __('The file upload was aborted.');
				break;
				case UPLOAD_ERR_NO_FILE:
					return __('No file was uploaded.');
				break;
				default:
					return sprintf(__('The file upload failed for an unknown reason. Error code: %d'), $error);
				break;
			}
		}
	}
}
