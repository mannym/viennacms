<?php
class AdminFileController extends Controller {
	public function folder() {
		$file = new Node();
		$file->node_id = $this->arguments[0];
		$file->read(true);

		$this->view->path = 'admin/file/upload.php';

		$prefix = '';

		if ($file->node_id) {
			admincontroller::set_context('node', $file);
		}
		
		$max_size = cms::$helpers->return_bytes(ini_get('upload_max_filesize')) / 1024 / 1024;
		
		$this->view['max_size'] = $max_size . ' MB';
		$this->view['action'] = view::url('admin/controller/file/upload_handler/' . $this->arguments[0]);

		//$this->view['data'] = $prefix;
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
			admincontroller::set_context('node', $file);
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
			 			
			unlink(VIENNACMS_PATH . $file->description);
			$file->delete();
			
			// it was a fun test, and we're all impressed
			$this->view['data'] = __('The file has been successfully deleted.');
		}
	}
	
	public function upload() {
		//$this->view->path = 'admin/file/upload.php';
		
		$max_size = cms::$helpers->return_bytes(ini_get('upload_max_filesize')) / 1024 / 1024;
		
		$parent = new Node();
		$parent->node_id = $this->arguments[0];
		$parent->read(true);
		
		if (!$parent->title) {
			return __('The parent node does not exist.');
		}
		
		admincontroller::set_context('node', $parent);
		
		$this->view['max_size'] = $max_size . ' MB';
		$this->view['action'] = view::url('admin/controller/file/upload_handler/' . $this->arguments[0]);
	}
	
	public function upload_handler() {
		$data = array();
		
		if (!isset($_FILES['viennafile'])) {
			echo json_encode(
				array(
					'message' => __('No file was uploaded.')
				)
			);
			exit;
		}
		
		$data['file'] = $_FILES['viennafile'];
		
		if ($error = $this->validate_file($data['file'])) {
			echo json_encode(
				array(
					'message' => sprintf(__('%sERROR:%s'), '<span style="color: red;">', '</span>') . $error
				)
			);
			exit;
		}
		
		$target = 'files/' . md5(uniqid(time())) . '.upload';
		
		move_uploaded_file($data['file']['tmp_name'], VIENNACMS_PATH . $target);
		
		$node = new FileNode();
		$node->initialize();
		$node->parent = $this->arguments[0];
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
		
		//return __('The file has successfully been uploaded.');
		echo json_encode(
			array(
				'message' => sprintf(__('Successfully uploaded %s'), $node->title),
				'addendum' => array(
					'ux_html' => '<li class="oncontentremove"><a class="file mynewnode" href="' . view::url('admin/controller/file/file/' . $node->node_id) . '">' . $node->title . '</a></li>'
				)
			)
		);
		exit;
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
