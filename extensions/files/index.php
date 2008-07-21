<?php
/**
 * File extension for viennaCMS, for uploading and downloading
 * 
 * @package viennaCMS
 * @author viennacms.nl
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License 
 */

if (! defined ( 'IN_VIENNACMS' )) {
	exit () ;
}
/**
 * Class for file upload/download
 *
 */
class extension_files {
	function admin_init() {
		$css = <<<CSS
.nodes a.file { background: url(../extensions/files/file.png) 0 0 no-repeat; }
.nodes a.fileroot { background: url(../extensions/files/folder.png) 0 0 no-repeat; }	
CSS;
		utils::add_css('inline', $css);
	}

	function list_types() {
		return array(
			'file' => array(
				'extension' => 'files',
				'type' => NODE_NO_REVISION,
				'allow_easy' => false
			),
			'fileroot' => array(
				'extension' => 'files',
				'type' => NODE_NO_REVISION,
				'allow_easy' => false
			)
		);
	}
	
	// hide in default system
	
	function file_allow_as_child($node) {
		return false;
	}
	
	function fileroot_allow_as_child($node) {
		return false;
	}
	
	function file_show_to_visitor($node) {
		return false;
	}
	
	function fileroot_show_to_visitor($node) {
		return false;
	}
	
	function file_in_tree($node) {
		return defined('IN_FILES');
	}
	
	function fileroot_in_tree($node) {
		return defined('IN_FILES');
	}
	
	function display_node($type, $node, $other) {
		if ($node->type == 'fileroot' || $node->type == 'file') {
			switch ($type) {
				case 'other_under_this':
				case 'show_to_visitor':
					return false;
				break;
				case 'in_tree':
					return defined('IN_FILES');
				break;
				case 'this_under_other':
					return false;
				break;
				default:
					return true;
				break;
			}
		} else if ($other->type == 'fileroot' || $other->type == 'file') {
			switch ($type) {
				case 'this_under_other':
					return false;
				break;
			}
		}
	}
	
	function create_root() {
		$node = CMS_Node::getnew();
		$node->parent_id = 0;
		$node->type = 'fileroot';
		$node->title = 'Files';
		$node->created = time();
		$node->extension = '';
		$node->description = '';
		$node->parentdir = '';
		$node->title_clean = 'filerootvienna';
		$node->write();
		
		return $node;
	}
	
	function create_folder($name, $parent) {
		$node = CMS_Node::getnew();
		$node->parent_id = $parent->node_id;
		$node->type = 'fileroot';
		$node->title = $name;
		$node->created = time();
		$node->extension = '';
		$node->description = '';
		$node->parentdir = '';
		$node->title_clean = utils::clean_title($name);
		$node->write();
		
		return $node;	
	}
	
	function get_root($create = true) {
		$node = new CMS_Node();
		$node->node_id = 0;
		$nodes = $node->get_children();
		
		foreach ($nodes as $node) {
			if ($node->type == 'fileroot') {
				$root = $node;
				break;
			}
		}

		if (!isset($root)) {
			if ($create) {
				$this->create_root();
				$root = $this->get_root(false);
			} else {
				trigger_error('Could not create file root!', E_USER_ERROR);
			}
		}
		
		return $root;
	}
	
	function get_admin_tree() {
		$root = $this->get_root();
	
		echo $this->_get_admin_tree($root);
	}
	
	function _get_admin_tree($node, $list = '') {
		utils::get_types();
		
		$ext = utils::load_extension(utils::$types[$node->type]['extension']);
		$show = true;
		if (method_exists($ext, $node->type . '_in_tree')) {
			$function = $node->type . '_in_tree';
			$show = $ext->$function($node);
		}
		
		if ($show) {
			if ($node->node_id != 0) {
				$list .= '<li><a href="index.php?action=show_actions&amp;id=files&amp;node=' . $node->node_id . '" class="' . $node->type . '">' . $node->title . '</a>' . "\r\n";			
			}
			
			$nodes = $node->get_children();
			
			if ($nodes) {
				$list .= '<ul>';
				foreach ($nodes as $node) {
					$list = $this->_get_admin_tree($node, $list);
				}
				$list .= '</ul>';
			}
			
			$list .= '</li>';
		}
		return $list;
	}
		
	function _get_select_tree($node, $list = '', $name = '') {
		utils::get_types();
		
		$ext = utils::load_extension(utils::$types[$node->type]['extension']);
		$show = true;
		if (method_exists($ext, $node->type . '_in_tree')) {
			$function = $node->type . '_in_tree';
			$show = $ext->$function($node);
		}
		
		if ($show) {
			if ($node->node_id != 0) {
				$list .= '<li id="' . $name . '-' . $node->node_id . '"><a href="javascript:void()" onclick="select_node(\'' . $name . '\', ' . $node->node_id . '); return false;" class="' . $node->type . '">' . $node->title . '</a>' . "\r\n";			
			}
			
			$nodes = $node->get_children();
			
			if ($nodes) {
				$list .= '<ul>';
				foreach ($nodes as $node) {
					$list = $this->_get_select_tree($node, $list, $name);
				}
				$list .= '</ul>';
			}
			
			$list .= '</li>';
		}
		return $list;
	}
		
		
	/**
	 * Display the upload form
	 */
	function upload_form ($folder_id) {
		$txt_file = __ ( 'File' ) ;
		$txt_desc = __ ( 'The file that should be uploaded' ) ;
		$txt_save = __ ( "Upload" ) ;
		$action	= admin::get_callback(array('files', 'admin_files'), array(
			'folder' => $folder_id,
			'mode' => 'save'
		));
		$content = <<<CONTENT
		 <form enctype="multipart/form-data" action="$action" class="upload" method="post">
			<table>
				<tr>
					<td width="70%">
						<strong>$txt_file</strong><br />
						$txt_desc
					</td>
					
					<td width="30%">
						<input name="file" id="file" type="file" />
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<input type="submit" value="$txt_save" />
					</td>
				</tr>
			</table>
		</form>
CONTENT;
		echo $content ;
		return ;
	}
	
	/**
	 * Move the file, and check some things
	 *
	 * @return string md5 
	 */
	function upload_file ( $file ) {
		if (! is_uploaded_file ( $file [ 'tmp_name' ] )) {
			return false ;
		}
		// TODO: add criteria like content type etc
		$new_path = $this->getuploaddir ( ROOT_PATH ) ;
		$md5_file = md5 ( uniqid ( time (), true ) ) ;
		$filename = $new_path . $md5_file . '.upload' ;
		if(!move_uploaded_file ( $file [ 'tmp_name' ], $filename )) {
			return false;
		}
		return $md5_file ;
	}
	
	/**
	 * Get the upload dir.
	 *
	 * @param string $path where we have to start searching
	 * @param string $uploadpath where we are gonna upload.
	 * @return unknown
	 */
	function getuploaddir ( $path , $uploadpath = 'files' ) {
		do {
			if (file_exists ( $path . 'config.php' ) && file_exists ( $path . 'start.php' )) {
				$root_path = $path ;
			} else {
				// On windows, it is not / but \ in our path.
				$pos = (strrpos ( $path, '/' )) ? strrpos ( $path, '/' ) : strrpos ( $path, '\\' ) ; // We need to escape this
				$path = substr ( $path, 0, $pos - 1 ) ;
				continue ;
			}
		} while ( empty ( $root_path ) ) ;
		
		return $root_path . '/' . $uploadpath . '/' ;
	}
	
	/**
	 * Handles the file upload
	 *
	 * @param string $filename given by the input type 'file'
	 * @return bool succes
	 */
	function handle_file_upload ( $folder, $filename ) {
		$db = database::getnew();
		$page = page::getnew(false);
		
		if (empty ( $_FILES [ $filename ] )) {
			return false ;
		}
		$file = $_FILES [ $filename ] ;
		if (intval ( $file [ 'error' ] ) != 0) {
			if ($file['error'] == 1) {
				$file['error'] = 'File too big.';
			}
			echo($file [ 'error' ]);
			return false ;
		}
		$md5 = $this->upload_file ( $file ) ;
		if(!$md5) {
			return false;
		}
		if (empty ( $file [ 'type' ] ) || strlen ( $file [ 'type' ] ) < 5) {
			echo ( __ ( "We need a valid mime content type" ) ) ;
			return false ;
		}
		$type = $file [ 'type' ];
		$name = $file [ 'name' ];
		
		if ($folder->type != 'fileroot') {
			echo(__('Invalid folder ID'));
			return false;
		}
		
		$parents = $page->get_parents($folder);
		$newnode_parentdir = '';
		foreach ($parents as $par) {
			$newnode_parentdir .= $par->title_clean . '/';
		}

		// hard way to strip first dir off
		$newnode_parentdir = substr($newnode_parentdir, strlen($parents[0]->title_clean . '/'));
		// strip trailing slash
		$newnode_parentdir = substr($newnode_parentdir, 0, -1);
		
		$pi = pathinfo($name);
		
		$node = CMS_Node::getnew();
		$node->parent_id = $folder->node_id;
		$node->type = 'file';
		$node->title = $name;
		$node->created = time();
		$node->extension = $pi['extension'];
		$node->description = $md5;
		$node->parentdir = $newnode_parentdir;
		$node->title_clean = $pi['filename'];
		$node->options['mimetype'] = $type;
		$node->write();
		
		// Datbase query
/*		$sql = "INSERT INTO " . UPLOADS_TABLE . "
				(filename, md5, type, time) VALUES(
				'" . $name . "',
				'" . $md5 . "',
				'" . $type . "',
				'" . time () . "')" ;
		if (! $db->sql_query ( $sql )) {
			return false ;
		}*/
		return $node;
	
	}
	
	/**
	 * Download a file
	 *
	 * @param int $file_id
	 * @return bool succes, but it also gives the file.
	 */
	
	function download_file ( $file_id, $utype = 'normal' ) {
		global $db ;
		$node = new CMS_Node();
		$node->node_id = $file_id;
		$node->read();
		
		$type = $node->options['mimetype'];
		$filename = $node->title;
		$md5 = $node->description;
		
		// Put some things about our visitor in the database now
		
		// Get the ip from ouer downloader.
		$ip_addr = $db->sql_escape ( $_SERVER [ 'REMOTE_ADDR' ] ) ;
		
		// Maybe the downloader got here by a proxy.
		// If yes, we want that address.
		$forwarded_for = '';
		if (isset ( $_SERVER [ 'HTTP_X_FORWARDED_FOR' ] )) {
			$forwarded_for = $db->sql_escape ( $_SERVER [ 'HTTP_X_FORWARDED_FOR' ] ) ;
		}
		
		// User agent
		$user_agent = $db->sql_escape ( $_SERVER [ 'HTTP_USER_AGENT' ] ) ;
		
		// Do we have a referer?
		$referer = '';
		if(isset($_SERVER['HTTP_REFERER'])) {
			$referer = $db->sql_escape ( $_SERVER['HTTP_REFERER'] );
		}

		$suffix = ($utype == 'thumb') ? '.thumb' : '.upload';

		if (!file_exists($this->getuploaddir ( ROOT_PATH ) . $md5 . $suffix)) {
			trigger_error(__('This file does not exist'), E_USER_ERROR);
		}
		
		// Make the query
		
		if ($utype == 'normal') {
			$sql = 
			"INSERT INTO " . DOWNLOADS_TABLE . "
			(file_id, ip, forwarded_for, user_agent, referer, time) VALUES(
			'" . intval ( $file_id ) . "',
			'" . $ip_addr . "',
			'" . $forwarded_for . "',
			'" . $user_agent . "',
			'" . $referer . "',
			'" . time() . "');" ;
			// Now execute the query
			$db->sql_query($sql);
		}
		
		// one more download now
		$node->options['downloads']++;
		$node->write();
		
		// Content type
		header ( 'Content-type: ' . $type ) ;
		if (substr($type, 0, 6) != 'image/') {
			// Force the user to download the file
			header ( 'Content-Disposition: attachment; filename="' . $filename . '"' ) ;
		}
		// We don't want cache
		header ( "Cache-Control: no-cache, must-revalidate" ) ; // HTTP/1.1
		header ( "Expires: Mon, 26 Jul 1997 05:00:00 GMT" ) ; // Date in the past
		// Now read the file
		readfile ( $this->getuploaddir ( ROOT_PATH ) . $md5 . $suffix ) ;
		return true ;
	}
	
	function list_files () {
/*		global $db ;
		$sql = "SELECT * FROM " . UPLOADS_TABLE . "
				LIMIT $start,$count" ;
		$result = $db->sql_query ( $sql ) ;
		$rowset = $db->sql_fetchrowset ( $result ) ;
		return $rowset ;*/
		$root = $this->get_root();
		
		return $this->_list_files($root);
	}
	
	function _list_files($node, $list = array()) {
		$nodes = $node->get_children();
		
		foreach ($nodes as $node) {
			$list[] = $node;
			$list = $this->_list_files($node, $list);
		}
		
		return $list;
	}
	
	/**
	 * Extension info
	 */
	function extinfo () {
		return array ( 
			'version' => '0.0.1' , 
			'name' => __ ( 'Files extension' ) , 
			'description' => __ ( 'Extension for uploading and downloading files' ) 
		) ;
	}
	
	function url_full_parsers() {
		return array(
			// this one needs to go first...
			'@file-download/(.*\/)?(.+)(/nocount)\.(.+)*$@' => array(
				1 => 'download_folder',
				2 => 'download_filename',
				3 => 'download_nocount',
				4 => 'download_extension'
			),
			// and this one second
			'@file-download/(.*\/)?(.+)(\.thumb)\.(.+)*$@' => array(
				1 => 'download_folder',
				2 => 'download_filename',
				3 => 'download_thumb',
				4 => 'download_extension'
			),
			'@file-download/(.*\/)?(.+)\.(.+)*$@' => array(
				1 => 'download_folder',
				2 => 'download_filename',
				3 => 'download_extension'
			)
		);
	}
	
	function get_file($args) {
		$this->download_file($args['node_id'], $args['type']);
		exit;
	}
	
	function before_display() {
		if (isset($_GET['download_filename'])) {
			$node = new CMS_Node();
			$node->title_clean	= $_GET['download_filename'];
			$node->parentdir	= substr($_GET['download_folder'], 0, -1);	
			$node->extension 	= $_GET['download_extension'];
			$node->read(NODE_TITLE);
			$this->download_file($node->node_id);
			exit;
		}
	}
	
	public function generate_thumbnail($source, $dest, $type) {
		// Set a maximum height and width
		$width = 500;

		// Get new dimensions
		list($width_orig, $height_orig) = getimagesize($source);

		$height = $height_orig;
		
		$ratio_orig = $width_orig/$height_orig;

		if ($width/$height > $ratio_orig) {
		   $width = $height*$ratio_orig;
		} else {
		   $height = $width/$ratio_orig;
		}

		// Resample
		$image_p = imagecreatetruecolor($width, $height);
		switch ($type) {
			case 'image/jpeg':
				$image = imagecreatefromjpeg($source);
			break;
			case 'image/gif':
				$image = imagecreatefromgif($source);
			break;
			case 'image/png':
				$image = imagecreatefrompng($source);
			break;
		}
		imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);

		// Output
		switch ($type) {
			case 'image/png':
				imagepng($image_p, $dest);
			break;
			case 'image/gif':
				imagegif($image_p, $dest);
			break;
			case 'image/jpeg':
				imagejpeg($image_p, $dest, 90);
			break;
		}
	}
	
	public function get_widget($matches) {
		$page = page::getnew(false);
		$node = new CMS_Node();
		$node->node_id = $matches[1];
		$node->read();

		$filename = $this->getuploaddir ( ROOT_PATH ) . $node->description . '.upload';
		$filesize = round(filesize($filename) / 1024, 2);
		
		$widget = '';
		switch ($node->options['mimetype']) {
			case 'image/gif':
			case 'image/png':
			case 'image/jpeg':
				if (extension_loaded('gd') && function_exists('imagecreatetruecolor')) {
					$image = getimagesize($filename);
					$thumbname = $this->getuploaddir ( ROOT_PATH ) . $node->description . '.thumb';
					if (!file_exists($thumbname)) {
						$this->generate_thumbnail($filename, $thumbname, $node->options['mimetype']);
					}
					$oldtitle = $node->title_clean;
					$node->title_clean .= '.thumb';
					$widget .= '<img src="' . $page->get_link($node) . '" alt="' . $node->title . '" /><br />';
					$node->title_clean = $oldtitle;
					$thumbnail = getimagesize($thumbname);
					if ($thumbnail[0] != $image[0]) {
						$widget .= '<a href="' . $page->get_link($node) . '">' . sprintf(__('View the full image'), $node->title, $filesize) . '</a>';
					}
					break;
				} // if else, no break
			default:
				$widget .= '<a href="' . $page->get_link($node) . '"><img style="float: left; border: 0px;" src="adm/images/download.png" alt="' . __('File') . '" /><span style="float: left; padding-top: 5px;">' . sprintf(__('Download %s<br />(%d kB)'), $node->title, $filesize) . '</span></a><br style="clear: both;" />';
			break;
		}
		
		$replacement = str_replace('{viennafile:' . $matches[1] . '}', $widget, $matches[0]);
		
		return $replacement;
	}
	
	function url_callbacks() {
		$node = $this->get_root();
		$core = utils::load_extension('core');
		return $core->recursive_urls($node, array());
	}
	
	function get_url_callback($node) {
		if ($node->type == 'file') {
			$return = array();
			// normal counted files
			$path = $node->title_clean;
			if ($node->parentdir) {
				$path = $node->parentdir . '/' . $path; 
			}
			if ($node->extension) {
				$path .= '.' . $node->extension; 
			}
			$path = 'file-download/' . $path;
			$return[$path] = array(
				'callback' => array('extension_files', 'get_file'),
				'cbtype' => 'create_new',
				'parameters' => array(
					'node_id' => $node->node_id,
					'type' => 'normal'
				)
			);
			// nocount files
			$ncpath = $node->title_clean;
			if ($node->parentdir) {
				$ncpath = $node->parentdir . '/' . $ncpath; 
			}
			$ncpath .= '/nocount';
			if ($node->extension) {
				$ncpath .= '.' . $node->extension; 
			}
			$ncpath = 'file-download/' . $ncpath;
			$return[$ncpath] = array(
				'callback' => array('extension_files', 'get_file'),
				'cbtype' => 'create_new',
				'parameters' => array(
					'node_id' => $node->node_id,
					'type' => 'nocount'
				)
			);
			// thumbnails
			$thpath = $node->title_clean;
			if ($node->parentdir) {
				$thpath = $node->parentdir . '/' . $thpath; 
			}
			$thpath .= '.thumb';
			if ($node->extension) {
				$thpath .= '.' . $node->extension; 
			}
			$thpath = 'file-download/' . $thpath;
			$return[$thpath] = array(
				'callback' => array('extension_files', 'get_file'),
				'cbtype' => 'create_new',
				'parameters' => array(
					'node_id' => $node->node_id,
					'type' => 'thumb'
				)
			);
			
			return $return;
		}
	}
	
	/**
	* Generates a node selector.
	*/
	
	function node_select($name, $callback = false) {
		$node = $this->get_root();
		$text = '<ul class="nodes">';
		$text .= $this->_get_select_tree($node, '', $name, $callback);
		$text .= '</ul>';
		$text .= '<input type="hidden" name="' . $name . '" id="' . $name . '" />';
		
		return $text;
	}

	function admin_get_actions($id) {
		utils::get_types();
		$node = new CMS_Node();
		$node->node_id = intval($_GET['node']);
		$node->read();	
		if ($id == 'files') {
			if ($node->type == 'fileroot') {
				return array(
					'options' => array(
						'title' => __('Options'),
						'image' => 'adm/style/images/applications.png',
						'data' => array(
							'newfolder' => array(
								'title' => __('Create a new folder'),
								'callback' => array('files', 'admin_files'),
								'params' => array(
									'mode' => 'folder',
									'node' => $_GET['node']
								),
								'image' => 'adm/images/add.png',
								'description' => __('Create a new folder under this folder.')
							),
							'upload_file' => array(
								'title' => __('Upload a new file'),
								'callback' => array('files', 'admin_files'),
								'params' => array(
									'mode' => 'upload',
									'node' => $_GET['node']
								),
								'image' => 'adm/images/edit.png',
								'description' => __('Upload a new file in this folder.')
							),
						)
					),
					'structure' => array(
						'title' => __('Structure'),
						'image' => 'adm/style/images/applications.png',
						'data' => array(
							'folder_delete' => array(
								'title' => __('Delete folder'),
								'callback' => array('files', 'admin_files'),
								'params' => array(
									'node' => $_GET['node'],
									'mode' => 'deletefolder'
								),
								'image' => 'adm/images/edit_remove.png',
								'description' => __('Delete this folder permanently.')
							)
						)
					),
				);
			} else if ($node->type == 'file') {
				return array(
					'options' => array(
						'title' => __('Options'),
						'image' => 'adm/style/images/applications.png',
						'data' => array(
							'file_delete' => array(
								'title' => __('Delete file'),
								'callback' => array('files', 'admin_files'),
								'params' => array(
									'node' => $_GET['node'],
									'mode' => 'deletefile'
								),
								'image' => 'adm/images/edit_remove.png',
								'description' => __('Delete this file permanently.')
							),
							'file_downloads' => array(
								'title' => __('View downloads'),
								'callback' => array('files', 'admin_files'),
								'params' => array(
									'node' => $_GET['node'],
									'mode' => 'downloadpopup'
								),
								'image' => 'adm/images/revisions.png',
								'description' => sprintf(__('View all downloads of this file. Download count: %d'), $node->options['downloads'])
							),
						)
					)
				);
			}
		}
	}
	
	function admin_get_mainitems() {
		return array(
			'files'	=> array(
				'image' => 'adm/style/images/files.png',
				'title'	=> __('Files'),
				'extension'	=> 'files',
			),
		);
	}
	
	function admin_files($args)
	{
		global $cache, $db;
		//$files = utils::load_extension('files');
		
		if(isset($args['mode']))
			$mode = $args['mode'];
		else
			$mode = (isset($_REQUEST['mode'])) ? $_REQUEST['mode'] : 'detail';

		switch($mode) {
			case 'save':
				$folder_id = $args['folder'];
				$folder = new CMS_Node();
				$folder->node_id = $folder_id;
				$folder->read();
				
				$new = $this->handle_file_upload($folder, 'file');
				$cache->destroy('_url_callbacks_' . md5(''));
				$cache->destroy('_url_callbacks_' . md5($_SERVER['HTTP_HOST']));
				echo 'reload';
				//header('Location: admin_files.php?mode=options&node=' . $new->node_id);
				exit;
			break;
				
			case 'upload':
				if (isset($args['node'])) {
					$folder_id = intval($args['node']);
					$folder = new CMS_Node();
					$folder->node_id = $folder_id;
					$folder->read();
				} else {
					$folder = $this->get_root();
				}
				if(!is_writable(ROOT_PATH . 'files/')) {
					echo '<div style="color: red;">' . 
					sprintf(__('Folder %s is not writable'), 'files/') . 
					'</div>';
					return false;
				}
				echo '<h1>' . sprintf(__('Upload a new file in %s'), $folder->title) . '</h1>';
				$this->upload_form($folder->node_id);
			break;
			case 'savefolder':
				$node_id = $_POST['parent'];
				$node = new CMS_Node();
				$node->node_id = intval($node_id);
				$node->read();
				$new = $this->create_folder($_POST['name'], $node);
				echo 'reload';
				exit;
			break;
			case 'folder':
				?>
				<form action="<?php echo admin::get_callback(array('files', 'admin_files'), array('mode' => 'savefolder')); ?>" method="post" style="padding-top: 10px;">
					<?php echo __('Folder name') ?>: 
					<input type="text" name="name" /><br />
					<input type="hidden" name="parent" value="<?php echo $args['node'] ?>" />
					<input type="submit" value="<?php echo __('Save') ?>" />
				</form>
				<?php
			break;
			case 'deletefile':
				?>
				<form action="<?php echo admin::get_callback(array('files', 'admin_files'), array('mode' => 'deletefiledo')); ?>" method="post">
					<div style="color: red;"><?php echo __("Are you sure you want to delete this file? This cannot be undone."); ?></div>
					<input type="hidden" name="node" value="<?php echo $args['node']; ?>" />
					<input type="submit" name="submit" value="<?php echo __("Submit"); ?>" />
				</form>
				<?php
			break;
			
			case 'deletefiledo':
				$node_id = $_POST['node'];
				$node = new CMS_Node();
				$node->node_id = intval($node_id);
				$node->read();
				@unlink($this->getuploaddir ( ROOT_PATH ) . $node->description . '.upload');
				$sql = "DELETE FROM " . NODES_TABLE . "
						 WHERE node_id = " . $node->$node_id;
				$db->sql_query($sql);
		
				echo 'reload';
				exit;		
			break;
			case 'deletefolder':
				?>
					<div style="color: red;"><?php echo __("Do you want to delete the files and folders in this folder, or move them to the parent folder?"); ?></div>
				<form action="<?php echo admin::get_callback(array('files', 'admin_files'), array('mode' => 'deletefolderdo', 'do' => 'delete')); ?>" method="post" style="display: inline;">
					<input type="hidden" name="node" value="<?php echo $args['node']; ?>" />
					<input type="submit" name="submit" value="<?php echo __("Delete"); ?>" />
				</form>
				<form action="<?php echo admin::get_callback(array('files', 'admin_files'), array('mode' => 'deletefolderdo', 'do' => 'move')); ?>" method="post" style="display: inline;">
					<input type="hidden" name="node" value="<?php echo $args['node']; ?>" />
					<input type="submit" name="submit" value="<?php echo __("Move"); ?>" />
				</form>
				<?php
			break;
			case 'deletefolderdo':
				$node_id = intval($_POST['node']);
				$node = new CMS_Node();
				$node->node_id = $node_id;
				$node->read();
				if ($args['do'] == 'move') {
					if ($node->parent_id == 0) {
						echo __('You cannot delete the root');
						exit;
					}
					$sql = "DELETE FROM " . NODES_TABLE . "
							WHERE node_id = " . $node_id;
					$db->sql_query($sql);
					$sql = "UPDATE " . NODES_TABLE . " SET parent_id = {$node->parent_id} WHERE parent_id = {$node_id}";
					$db->sql_query($sql);
				} else if ($args['do'] == 'delete') {
					if ($node->parent_id == 0) {
						echo __('You cannot delete the root');
						exit;
					}
					
					$this->recursive_delete($node);
				}
		
				echo 'reload';
				exit;
				return;
			break;
			
			case 'options':
			default:
				if (!isset($args['node'])) {
					$node = $this->get_root();
				} else {
					$node_id = intval($args['node']);
					$node = new CMS_Node();
					$node->node_id = $node_id;
					$node->read();
				}
				$page = page::getnew(false);
				
				switch ($node->type) {
					case 'fileroot':
						?>
						<h1><?php echo sprintf(__('Actions for %s'), $node->title); ?></h1>
						<p class="icon_p"><a href="admin_files.php?node=<?php echo $node->node_id ?>&amp;mode=folder"><img src="images/add.png" /><br /><?php echo __('Create a new folder') ?></a><br /><?php echo __('Create a new folder under this folder.') ?></p>
						<p class="icon_p"><a href="admin_files.php?node=<?php echo $node->node_id ?>&amp;mode=upload"><img src="images/edit.png" /><br /><?php echo __('Upload a new file') ?></a><br /><?php echo __('Upload a new file in this folder.') ?></p>
						<p class="icon_p"><a href="admin_files.php?node=<?php echo $node->node_id ?>&amp;mode=deletefolder"><img src="images/edit_remove.png" /><br /><?php echo __('Delete') ?></a><br /><?php echo __('Delete this folder from the file system') ?></p>
						<?php
					break;
					case 'file':
						?>
						<h1><?php echo sprintf(__('Actions for %s'), $node->title); ?></h1>
						<p class="icon_p"><a href="admin_files.php?node=<?php echo $node->node_id ?>&amp;mode=deletefile"><img src="images/edit_remove.png" /><br /><?php echo __('Delete') ?></a><br /><?php echo __('Delete this file from the file system') ?></p>
						<p class="icon_p"><a href="<?php echo '../' . $page->get_link($node) ?>"><img src="images/revisions.png" /><br /><?php echo __('Download') ?></a><br /><?php echo __('Download this file') ?></p>
						<div>Total downloads: <?php echo $node->options['downloads'] ?>.<p />
						<a href="#" onclick="window.open('./admin_files.php?mode=downloadpopup&node=<?php echo $node->node_id ?>', 'downloadpopup', 'height=500px,scrollbars=yes,width=800px');"><?php echo __('View all download details'); ?></a>
						<?php
					break;
				}
			break;
			
			case 'downloadpopup':
				if (!isset($args['node'])) {
					$node = $this->get_root();
				} else {
					$node_id = intval($args['node']);
					$node = new CMS_Node();
					$node->node_id = $node_id;
					$node->read();
				}
				$start = empty($args['start']) ? 0 : intval($args['start']);
				$end = $start + 10;
				$db = database::getnew();
				$dl_count = $node->options['downloads'];
				$page = page::getnew(false);
				$page->sitenode->options['rewrite'] = 'on';
				$sql = 'SELECT *
						FROM ' . DOWNLOADS_TABLE . '
						WHERE file_id = ' . intval($node->node_id) . "
						ORDER BY time DESC
						LIMIT $start,$end";
				$result = $db->sql_query($sql);
				?>
				<h1><?php echo sprintf(__('Downloads for %s'), $node->title); ?></h1>
				<table>
					<tr>
						<th width="100px"><?php echo __('IP address') ?></th>
						<th width="200px"><?php echo __('Referer') ?></th>
						<th width="200px;"><?php echo __('User agent') ?></th>
						<th width="100px"><?php echo __('Time') ?></th>
					</tr>
					<?php
					while ($row = $db->sql_fetchrow($result)) {
						?>
					<tr>
						<td><?php echo (!empty($row['forwarded_for'])) ? $row['forwarded_for'] : $row['ip'] ?></td>
						<td><?php echo wordwrap(htmlspecialchars($row['referer']), 30, "<br />\r\n", true) ?></td>
						<td><?php echo htmlspecialchars($row['user_agent']) ?></td>
						<td> <?php echo date('d-m-Y G:i:s', $row['time']) ?></td>
					</tr><?php
					}?>
				</table>
				<?php if(!$start < $dl_count) {
					$pargs = $args;
					$pargs['start'] = $start - 10;
				?>
				<a href="<?php echo admin::get_callback(array('files', 'admin_files'), $pargs) ?>">&laquo;&laquo;</a> 
				<?php } ?>
				<?php if($dl_count > $end) {
					$pargs = $args;
					$pargs['start'] = $start + 10;
				?>
				<div style="text-align: right; padding-right: 50px;"><a href="<?php echo admin::get_callback(array('files', 'admin_files'), $pargs) ?>">&raquo;&raquo;</a></div>
				<?php }
			break;
		}		
	}

		
	function recursive_delete($node) {
		$db = database::getnew();
		@unlink($this->getuploaddir ( ROOT_PATH ) . $node->description . '.upload');
		$sql = "DELETE FROM " . NODES_TABLE . "
				 WHERE node_id = " . $node->node_id;
		$db->sql_query($sql);
	
		$nodes = $node->get_children();
		foreach ($nodes as $cnode) {
			$this->recursive_delete($cnode);
		}
		
	}
	
	function admin_left_files()
	{
		define('IN_FILES', true); // this is why it wouldn't work
		?>
		<ul class="nodes">
		<?php
		$this->get_admin_tree();
		?>
		</ul>
		<?php
	}
}
?>