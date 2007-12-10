<?php
 /**
 * Add a new node
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
$parent = new CMS_Node();
$parent->node_id = $_GET['node'];
$parent->read();
$node = CMS_Node::getnew();

switch($mode) {
	case 'save':
		$post_vars = array('node_id', 'type', 'title', 'description', 'title_clean', 'parentdir', 'extension');
		foreach($post_vars as $postvar) {
		if(empty($_POST[$postvar]) && $postvar != ('parentdir' || 'extension')) {
				trigger_error(__($postvar . '  not given!'), E_USER_ERROR);
				return;
			}
			$var = 'newnode_' . $postvar;
			$$var = $db->sql_escape($_POST[$postvar]); 
		}
		$node->parent_id = ($newnode_type == 'site' ? 0 : $newnode_node_id);
		$node->type = ($newnode_type == '--' . __('Select') . '--') ? 'page' : $newnode_type;
		$node->created = time();
		$node->title = $newnode_title;
		$node->extension = $newnode_extension;
		$node->description = $newnode_description;
		$node->parentdir = $newnode_parentdir;
		$node->title_clean = utils::clean_title($node_title_clean);
		$node->write();
		$new_id = mysql_insert_id();
		header('Location: ' . utils::base() . 'admin_node.php?node=' . $node->node_id);		
	break;
		
	default:
	case 'form':
		// TODO: re-enable this code, but with prettier formatting :)
		/* $sql			= "SELECT node_id, title FROM " . NODES_TABLE;
		$result			= $db->sql_query($sql);
		$parent_options	= $db->sql_fetchrowset($result); */
		$type_options	= utils::run_hook_all('list_types');
		$page_title		= __('viennaCMS ACP - Add a new node');
		include('./header.php');
		?>
		<h1><?php echo sprintf(__('Add a new node under %s'), $parent->title); ?></h1>
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
						<?php echo __('Specify the type of the new node'); ?>
					</td>
					<td>
						<select name="type">
							<option name="">--<?php echo __('Select') ?>--</option>
							<?php foreach($type_options as $type) {
								?><option name="<?php echo $type; ?>"><?php echo $type; ?></option><?php
							} ?></select>
					</td>
				</tr>
				
				<tr>
					<td>
						<strong> <?php echo __('Title'); ?></strong><br />
						<?php echo __('Enter the title for the new node. This title will be automatically cleaned.') ?>
					</td>
					<td>
						<input type="text" name="title" id="title" />
					</td>
				</tr>
				<tr>
					<td>
						<strong> <?php echo __('Clean Title'); ?></strong><br />
						<?php echo __('The clean title for the node. If empty, this will be automatically generated. Also, when changing the title, this will also be generated.') ?>
					</td>
					<td>
						<input type="text" name="title_clean" id="title_clean" />
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
						<?php echo __('Enter the description for the new node') ?>
					</td>
					<td>
						<textarea rows="3" cols="28" name="description"></textarea>
					</td>
				</tr>
								
				<tr>
					<td colspan="2">
						<input type="hidden" name="node_id" value="<?php echo $parent->node_id; ?>" />
						<input type="submit" name="submit" value="<?php echo __('Save') ?>" />
					</td>
				</tr>
			</table>
		</form>
		<script type="text/javascript">
			function showParentDirExample() {
				alert("If your clean title is 'bla', and your parent dir is 'test', then the url for this node will be test/bla.html, if the rewrite function is enabled");
				return;
			} 
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