<?php
/**
 * Edit a node
 *  
 * @package viennaCMS
 * @author viennainfo.nl
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

define('IN_VIENNACMS', true);
define('IN_FILES', true);
include('../start.php');
$user = user::getnew();
$user->checkacpauth();
$files = utils::load_extension('files');

$mode = (isset($_REQUEST['mode'])) ? $_REQUEST['mode'] : 'detail';

$page_title = __('viennaCMS ACP - File admin');

switch($mode) {
	case 'save':
		$folder_id = $_POST['folder'];
		$folder = new CMS_Node();
		$folder->node_id = $folder_id;
		$folder->read();
		
		$new = $files->handle_file_upload($folder, 'file');
		$cache->destroy('_url_callbacks_' . md5(''));
		$cache->destroy('_url_callbacks_' . md5($_SERVER['HTTP_HOST']));
		header('Location: admin_files.php?mode=options&node=' . $new->node_id);
		exit;
	break;
		
	case 'upload':
		include('./header.php');
		if (isset($_GET['node'])) {
			$folder_id = $_GET['node'];
			$folder = new CMS_Node();
			$folder->node_id = $folder_id;
			$folder->read();
		} else {
			$folder = $files->get_root();
		}
		if(!is_writable(ROOT_PATH . 'files/')) {
			echo '<div style="color: red;">' . 
			sprintf(__('Folder %s is not writable'), 'files/') . 
			'</div>';
			include('./footer.php');
			die();
		}
		echo '<h1>' . sprintf(__('Upload a new file in %s'), $folder->title) . '</h1>';
		$files->upload_form($folder->node_id);
		include('./footer.php');
	break;
	case 'savefolder':
		$node_id = $_POST['parent'];
		$node = new CMS_Node();
		$node->node_id = $node_id;
		$node->read();
		$new = $files->create_folder($_POST['name'], $node);
		
		header('Location: admin_files.php?mode=options&node=' . $new->node_id);
		exit;
	break;
	case 'folder':
		include('./header.php');
		?>
		<form action="?mode=savefolder" method="post" style="padding-top: 10px;">
			<?php echo __('Folder name') ?>: 
			<input type="text" name="name" /><br />
			<input type="hidden" name="parent" value="<?php echo $_GET['node'] ?>" />
			<input type="submit" value="<?php echo __('Save') ?>" />
		</form>
		<?php
		include('./footer.php');
	break;
	case 'deletefile':
		include('./header.php');
		?>
		<form action="?mode=deletefiledo" method="post">
			<div style="color: red;"><?php echo __("Are you sure you want to delete this file? This cannot be undone."); ?></div>
			<input type="hidden" name="node" value="<?php echo $_GET['node']; ?>" />
			<input type="submit" name="submit" value="<?php echo __("Submit"); ?>" />
		</form>
		<?php include('./footer.php');
	break;
	case 'deletefiledo':
		$node_id = $_POST['node'];
		$node = new CMS_Node();
		$node->node_id = $node_id;
		$node->read();
		@unlink($files->getuploaddir ( ROOT_PATH ) . $node->description . '.upload');
		$sql = "DELETE FROM " . NODES_TABLE . "
				 WHERE node_id = " . $node_id;
		$db->sql_query($sql);

		header('Location: admin_files.php?mode=options&node=' . $node->parent_id);
		exit;		
	break;
	case 'deletefolder':
		include('./header.php');
		?>
			<div style="color: red;"><?php echo __("Do you want to delete the files and folders in this folder, or move them to the parent folder?"); ?></div>
		<form action="?mode=deletefolderdo&amp;do=delete" method="post" style="display: inline;">
			<input type="hidden" name="node" value="<?php echo $_GET['node']; ?>" />
			<input type="submit" name="submit" value="<?php echo __("Delete"); ?>" />
		</form>
		<form action="?mode=deletefolderdo&amp;do=move" method="post" style="display: inline;">
			<input type="hidden" name="node" value="<?php echo $_GET['node']; ?>" />
			<input type="submit" name="submit" value="<?php echo __("Move"); ?>" />
		</form>
		<?php include('./footer.php');
	break;
	case 'deletefolderdo':
		$node_id = intval($_POST['node']);
		$node = new CMS_Node();
		$node->node_id = $node_id;
		$node->read();
		if ($_GET['do'] == 'move') {
			if ($node->parent_id == 0) {
				trigger_error(__('You cannot delete the root'), E_USER_ERROR);
			}
			$sql = "DELETE FROM " . NODES_TABLE . "
					WHERE node_id = " . $node_id;
			$db->sql_query($sql);
			$sql = "UPDATE " . NODES_TABLE . " SET parent_id = {$node->parent_id} WHERE parent_id = {$node_id}";
			$db->sql_query($sql);
		} else if ($_GET['do'] == 'delete') {
			if ($node->parent_id == 0) {
				trigger_error(__('You cannot delete the root'), E_USER_ERROR);
			}
			
			recursive_delete($node);
		}

		header('Location: admin_files.php?mode=options&node=' . $node->parent_id);
		exit;		
	break;
	
	case 'options':
	default:
		include('./header.php');
		if (!isset($_GET['node'])) {
			$node = $files->get_root();
		} else {
			$node_id = intval($_GET['node']);
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
		include('./footer.php');
	break;
	
	case 'downloadpopup':
		if (!isset($_GET['node'])) {
			$node = $files->get_root();
		} else {
			$node_id = intval($_GET['node']);
			$node = new CMS_Node();
			$node->node_id = $node_id;
			$node->read();
		}
		$start = empty($_GET['start']) ? 0 : intval($_GET['start']);
		$end = $start + 10;
		$db = database::getnew();
		$dl_count = $node->options['downloads'];
		$page = page::getnew(false);
		$page->sitenode->options['rewrite'] = 'on';
		$sql = 'SELECT *
				FROM ' . DOWNLOADS_TABLE . '
				WHERE file_id = ' . intval($node->node_id) . "
				LIMIT $start,$end";
		$result = $db->sql_query($sql);
		?><html>
	<head>
		<title><?php echo sprintf(__('Downloads for %s'), $node->title); ?></title>
	</head>
	<body>
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
		<?php if(!$start < $dl_count) { ?>
		<a href="?mode=downloadpopup&amp;node=<?php echo $node->node_id ?>&amp;start=<?php echo $start - 10 ?>">&laquo;&laquo;</a> 
		<?php } ?>
		<div style="text-align: center;"><a href="#" onclick="window.close();"><?php echo __('Close window') ?></a></div>
		<?php if($dl_count > $end) { ?>
		<div style="text-align: right;"><a href="?mode=downloadpopup&amp;node=<?php echo $node->node_id ?>&amp;start=<?php echo $start + 10 ?>">&raquo;&raquo;</a></div>
		<?php } ?>
	</body>
</html><?php
	break;
}		

function recursive_delete($node) {
	global $files;
	
	$db = database::getnew();
	@unlink($files->getuploaddir ( ROOT_PATH ) . $node->description . '.upload');
	$sql = "DELETE FROM " . NODES_TABLE . "
			 WHERE node_id = " . $node->node_id;
	$db->sql_query($sql);

	$nodes = $node->get_children();
	foreach ($nodes as $cnode) {
		recursive_delete($cnode);
	}
	
}
?>