<?php
/**
 * File extension for viennaCMS, for uploading and downloading
 * 
 * @package viennaCMS
 * @author viennainfo.nl
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License 
 */

if (! defined ( 'IN_viennaCMS' )) {
	exit () ;
}
/**
 * Class for file upload/download
 *
 */
class extension_files {
	/**
	 * Display the upload form
	 */
	function upload_form () {
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
		move_uploaded_file ( $file [ 'tmp_name' ], $filename ) ;
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
	function handle_file_upload ( $filename ) {
		global $db ;
		if (empty ( $_FILES [ $filename ] )) {
			return false ;
		}
		$file = $_FILES [ $filename ] ;
		if (intval ( $file [ 'error' ] ) != 0) {
			trigger_error ( $file [ 'error' ], E_USER_ERROR ) ;
			return false ;
		}
		$md5 = $this->upload_file ( $file ) ;
		if (empty ( $file [ 'type' ] ) || strlen ( $file [ 'type' ] ) < 5) {
			trigger_error ( __ ( "We need a valid mime content type" ), E_USER_ERROR ) ;
			return false ;
		}
		$type = $db->sql_escape ( $file [ 'type' ] ) ;
		$name = $db->sql_escape ( $file [ 'name' ] ) ;
		// Datbase query
		$sql = "INSERT INTO " . UPLOADS_TABLE . "
				(filename, md5, type, time) VALUES(
				'" . $name . "',
				'" . $md5 . "',
				'" . $type . "',
				'" . time () . "')" ;
		if (! $db->sql_query ( $sql )) {
			return false ;
		}
		return true ;
	
	}
	
	/**
	 * Download a file
	 *
	 * @param int $file_id
	 * @return bool succes, but it also gives the file.
	 */
	
	function download_file ( $file_id ) {
		global $db ;
		$sql = "SELECT * FROM " . UPLOADS_TABLE . "
				WHERE upload_id = " . intval ( $file_id ) ;
		if (! $result = $db->sql_query ( $sql )) {
			trigger_error ( __ ( "No valid file id given!" ) ) ;
			return false ;
		}
		$file = $db->sql_fetchrow ( $result ) ;
		
		$type = $file [ 'type' ] ;
		$filename = $file [ 'filename' ] ;
		$md5 = $file [ 'md5' ] ;
		
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
		
		// one more download now
		$sql = 
		"UPDATE " . UPLOADS_TABLE . "
		SET downloaded = downloaded+1
		WHERE upload_id = " . intval ( $file_id );
		
		$db->sql_query($sql);
		
		// Content type
		header ( 'Content-type: ' . $type ) ;
		// Force the user to download the file
		header ( 'Content-Disposition: attachment; filename="' . $filename . '"' ) ;
		// We don't want cache
		header ( "Cache-Control: no-cache, must-revalidate" ) ; // HTTP/1.1
		header ( "Expires: Mon, 26 Jul 1997 05:00:00 GMT" ) ; // Date in the past
		// Now read the file
		readfile ( $this->getuploaddir ( ROOT_PATH ) . $md5 . '.upload' ) ;
		return true ;
	}
	
	function list_files ( $start = 0 , $count = 30 ) {
		global $db ;
		$sql = "SELECT * FROM " . UPLOADS_TABLE . "
				LIMIT $start,$count" ;
		$result = $db->sql_query ( $sql ) ;
		$rowset = $db->sql_fetchrowset ( $result ) ;
		return $rowset ;
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
}
?>