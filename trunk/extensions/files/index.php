<?php
/**
 * File extension for viennaCMS, for uploading and downloading
 * 
 * @package viennaCMS
 * @author viennainfo.nl
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
				'type' => NODE_NO_REVISION
			),
			'fileroot' => array(
				'extension' => 'files',
				'type' => NODE_NO_REVISION
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
	
	function get_root() {
		$node = new CMS_Node();
		$node->node_id = 0;
		$nodes = $node->get_children();
		
		foreach ($nodes as $node) {
			if ($node->type == 'fileroot') {
				$root = $node;
				break;
			}
		}
		
		if (!$root) {
			$root = $this->create_root();
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
				$list .= '<li><a href="admin_files.php?mode=options&amp;node=' . $node->node_id . '" class="' . $node->type . '">' . $node->title . '</a>' . "\r\n";			
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
		
	
	/**
	 * Display the upload form
	 */
	function upload_form ($folder_id) {
		$txt_file = __ ( 'File' ) ;
		$txt_desc = __ ( 'The file that should be uploaded' ) ;
		$txt_save = __ ( "Upload" ) ;
		$content = <<<CONTENT
		 <form enctype="multipart/form-data" action="?mode=save" method="post">
			<table>
				<tr>
					<td width="70%">
						<strong>$txt_file</strong><br />
						$txt_desc
					</td>
					
					<td width="30%">
						<input type="hidden" name="MAX_FILE_SIZE" value="10000000">
						<input type="hidden" name="folder" value="$folder_id" />
						<input name="file" type="file">
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
			trigger_error ( $file [ 'error' ], E_USER_ERROR ) ;
			return false ;
		}
		$md5 = $this->upload_file ( $file ) ;
		if(!$md5) {
			return false;
		}
		if (empty ( $file [ 'type' ] ) || strlen ( $file [ 'type' ] ) < 5) {
			trigger_error ( __ ( "We need a valid mime content type" ), E_USER_ERROR ) ;
			return false ;
		}
		$type = $file [ 'type' ];
		$name = $file [ 'name' ];
		
		if ($folder->type != 'fileroot') {
			trigger_error(__('Invalid folder ID'), E_USER_ERROR);
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
	
	function download_file ( $file_id ) {
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
		
		// Make the query
		
		if (!isset($_GET['download_nocount']) && !isset($_GET['download_thumb'])) {
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
		
		$suffix = (isset($_GET['download_thumb'])) ? '.thumb' : '.upload';
		
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
			default:
				$widget .= '<a href="' . $page->get_link($node) . '"><img style="float: left; border: 0px;" src="adm/images/download.png" /><span style="float: left; padding-top: 5px;">' . sprintf(__('Download %s<br />(%d kB)'), $node->title, $filesize) . '</span></a><br style="clear: both;" />';
			break;
		}
		
		$replacement = str_replace('{viennafile:' . $matches[1] . '}', $widget, $matches[0]);
		
		return $replacement;
	}
}
?>