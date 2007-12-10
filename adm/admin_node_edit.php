<?php
 /**
 * Edit a node
 *  
 * @package viennaCMS
 * @author viennainfo.nl
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

define('IN_VIENNACMS', true);
include('../start.php');
$user = user::getnew();
$user->checkacpauth();

$display_admin_tree = (empty($_GET['display_admin_tree']) ) ?  1 : 0;
$mode = isset($_GET['mode']) ? $_GET['mode'] : 'form';
$node = new CMS_Node();
$node->node_id = $_REQUEST['node'];
$node->read();

switch($mode) {
	case 'save':
		$post_vars = array('node', 'parent_id', 'type', 'title', 'description', 'title_clean', 'parentdir', 'extension');
		foreach($post_vars as $postvar) {
			if(empty($_POST[$postvar]) && $postvar != ('parentdir' || 'extension')) {
				trigger_error(__($postvar . '  not given!'), E_USER_ERROR);
				return;
			}
			$var = 'node_' . $postvar;
			$$var = $db->sql_escape($_POST[$postvar]); 
		}
		$node->parent_id = $node_parent_id;
		$node->node_id = intval($_POST['node']);
		$node->type = $node_type;
		$node->title_clean = utils::clean_title($node_title_clean);
		$node->parentdir = $node_parentdir;
		$node->extension = $node_extension;
		$node->created = time();
		$node->title = $node_title;
		$node->description = stripslashes($node_description);
		$node->write();
		header('Location: ' . utils::base() . 'admin_node.php?node=' . $node->node_id);		
	break;
		
	default:
	case 'form':
	
		$sql = "SELECT * FROM " . NODES_TABLE . "
		WHERE node_id = " . $node->node_id;
		if(!$result = $db->sql_query($sql)) {
			trigger_error("Oops!", E_USER_ERROR);
		}
		$node_info = $db->sql_fetchrow($result);
		// TODO: re-enable this code, but with prettier formatting :)
		/* $sql			= "SELECT node_id, title FROM " . NODES_TABLE;
		$result			= $db->sql_query($sql);
		$parent_options	= $db->sql_fetchrowset($result); */
		$type_options	= utils::run_hook_all('list_types');
		$page_title		= __('viennaCMS ACP - Edit this node');
		include('./header.php');
		?>
		<h1><?php echo sprintf(__('Edit the node %s'), $node->title); ?></h1>
		<form action="?mode=save" method="post">
			<table>
				<?php
				// TODO: re-enable this code, but with prettier formatting :)
				/*
				<tr>
					<td width="70%">
						<strong><?php echo __('Parent') ?>:</strong><br />
						<?php echo __('Enter the parent for the new node') ?>
					</td>
					<td width="30%">
						<select name="parent">
							<option name=""><?php echo __('None') ?></option>
							<?php foreach($parent_options as $parent) {
								?><option name="<?php echo $parent['node_id']; ?>"><?php echo $parent['title']; ?></option><?php
							} ?></select>
					</td>
				</tr>
				*/ ?>
				<tr>
					<td>
						<strong><?php echo __('Type') ?>:</strong><br />
						<?php echo __('Specify the type of the node'); ?>
					</td>
					<td>
						<select name="type">
							<option name="">--<?php echo __('Select') ?>--</option>
							<?php foreach($type_options as $type) {
								?><option name="<?php echo $type; if($type == $node_info['type']) { echo '" selected="selected'; } ?>"><?php echo $type; ?></option><?php
							} ?></select>
					</td>
				</tr>
				
				<tr>
					<td>
						<strong> <?php echo __('Title'); ?></strong><br />
						<?php echo __('Enter the title for the node. This title will be automatically cleaned.') ?>
					</td>
					<td>
						<input type="text" name="title" id="title" value="<?php echo $node_info['title']; ?>" />
					</td>
				</tr>
				<tr>
					<td>
						<strong> <?php echo __('Clean Title'); ?></strong><br />
						<?php echo __('The clean title for the node. If empty, this will be automatically generated. Also, when changing the title, this will also be generated.') ?>
					</td>
					<td>
						<input type="text" name="title_clean" id="title_clean" value="<?php echo $node_info['title_clean'] ?>" />
					</td>
				</tr>
				
				<tr>
					<td>
						<strong><?php echo __('Parent dir'); ?></strong><br />
						<?php echo __('The parent dir for the new node. This is not required.') ?><br />
						<a href="#" onclick="showParentDirExample();"><?php echo __("Show example") ?></a>
					</td>
					
					<td>
						<input type="text" name="parentdir" value="<?php echo $node_info['parentdir'] ?>" />
					</td>
				</tr>
				
				<tr>
					<td>
						<strong><?php echo __('Extension'); ?></strong><br />
						<?php echo __('Enter the extension. By example, html. Don\'t put a dot (\'.\') at the begin of the extension!.') ?><br />
					</td>
					
					<td>
						<input type="text" name="extension" value="<?php echo $node_info['extension'] ?>" />
					</td>
				</tr>
									
				<tr>
					<td>
						<strong><?php echo __('Description'); ?></strong><br />
						<?php echo __('Enter the description for the node') ?>
					</td>
					<td>
						<textarea rows="3" cols="28" name="description"><?php echo stripslashes($node_info['description']); ?></textarea>
					</td>
				</tr>
								
				<tr>
					<td colspan="2">
						<input type="hidden" name="node" value="<?php echo $node_info['node_id']; ?>" />
						<input type="hidden" name="parent_id" value="<?php echo  $node_info['parent_id']; ?>" />
						<input type="submit" name="submit" value="<?php echo __('Save') ?>" />
					</td>
				</tr>
			</table>
		</form>
		<script type="text/javascript">
			$('#title').blur(function () {
				$.get('<?php echo utils::base() ?>ajax.php?mode=cleantitle&title=' + escape($('#title').attr('value')), '', function(data, textStatus) {
					$('#title_clean').attr('value', data);
				});
			});
		</script>
		
		<?php include('./footer.php');
	break;
}		
?>