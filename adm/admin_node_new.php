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
$page = page::getnew(false);

$display_admin_tree = (empty($_GET['display_admin_tree']) ) ?  1 : 0;
$mode = isset($_GET['mode']) ? $_GET['mode'] : 'form';
$do = isset($_REQUEST['do']) ? $_REQUEST['do'] : 'new';
$easy = (isset($_POST['easy']));
if ($do == 'new') {
	$parent = new CMS_Node();
	$parent->node_id = (isset($_GET['node'])) ? $_GET['node'] : $_POST['node_id'];
	$parent->read();
	$node = CMS_Node::getnew();
	$node_id = $parent->node_id;
} else if ($do == 'edit') {
	$node_id = (isset($_GET['node'])) ? $_GET['node'] : $_POST['node_id'];
	$node = new CMS_Node();
	$node->node_id = $node_id;
	$node->read();
	$parents = $node->get_parent();
	$parent = $parents[0];
}

switch($mode) {
	case 'easy':
		include('./header.php');
		$type = explode('::', base64_decode($_GET['type']));
		$ext = utils::load_extension($type[0]);
		if (method_exists($ext, $type[1] . '_allow_as_child')) {
			$function = $type[1] . '_allow_as_child';
			$callback = array($ext, $function);
		} else {
			$callback = false;
		}
		echo '<h1>' . sprintf(__('Content wizard, step %d of %d'), 1, 4) . '</h1>';
		echo '<form action="?mode=form" method="post">';
		echo __('Where do you want to place this new node?') . '<br />';
		echo utils::node_select('node_id', $callback, 1);
		?>
		<input type="submit" value="<?php echo __('Next &raquo;') ?>" />
		<input type="hidden" name="type" value="<?php echo $_GET['type'] ?>" />
		<input type="hidden" name="easy" value="true" />
		<?php
		echo '</form>';
		include('./footer.php');
	break;
	case 'save':
		$post_vars = array('node_id', 'title', 'description', 'title_clean', 'extension');
		if ($do == 'new') {
			$post_vars[] = 'type';
		}
		foreach($post_vars as $postvar) {
			if(empty($_POST[$postvar]) && $postvar != 'extension') {
				trigger_error(__($postvar . '  not given!'), E_USER_ERROR);
				return;
			}
			$var = 'newnode_' . $postvar;
			$$var = $_POST[$postvar]; 
		}
		if ($do == 'new') {
			$node->parent_id = ($newnode_type == 'site' ? 0 : $newnode_node_id);
			$node->type = ($newnode_type == '--' . __('Select') . '--') ? 'page' : $newnode_type;
		}

		if ($parent) {
			$parents = $page->get_parents($parent);
			$newnode_parentdir = '';
			foreach ($parents as $par) {
				$newnode_parentdir .= $par->title_clean . '/';
			}

			// hard way to strip first dir off
			$newnode_parentdir = substr($newnode_parentdir, strlen($parents[0]->title_clean . '/'));
			// strip trailing slash
			$newnode_parentdir = substr($newnode_parentdir, 0, -1);
		}
		$newnode_title_clean = (empty($newnode_title_clean)) ? utils::clean_title($newnode_title) : $newnode_title_clean;
		
		if ($do == 'new') {
			$sql = 'SELECT node_id, title 
					FROM ' . NODES_TABLE . "
					WHERE title_clean = '$newnode_title_clean';";
			$result = $db->sql_query($sql); 
			if($row = $db->sql_fetchrow($result)) {
				trigger_error(__('There is already an node with the same clean title. This node is: ') .
				'<a href="' . $page->get_link($row['node_id']) . '">' . $row['title'] . '</a>.<br />
				Click <a href="./admin_node_new.php?do=edit&node=' . $newnode_node_id . '">here</a> to return to the previous page.', E_USER_ERROR);
			}
		}
		$node->created = time();
		$node->title = $newnode_title;
		$node->extension = $newnode_extension;
		$node->description = $newnode_description;
		$node->parentdir = $newnode_parentdir;
		$node->title_clean = $newnode_title_clean;
		$node->write();
		$parents = $page->get_parents($node);
		$sitenode = $parents[0];
		$sitehash = md5($sitenode->options['hostname']);
		$cache->destroy('_url_callbacks_' . $sitehash); 
		if (!$easy) {
			header('Location: ' . utils::base() . 'admin_node.php?node=' . $node->node_id);
		} else {
			header('Location: ' . utils::base() . 'admin_node_options.php?easy=true&node=' . $node->node_id);
		}
	break;
		
	default:
	case 'form':
		$type_options	= utils::run_hook_all('list_types');
		$page_title		= $do == 'edit' ? __('viennaCMS ACP - Edit a node') : __('viennaCMS ACP - Add a new node');
		include('./header.php');
		if (!$easy) {
			if ($do == 'new') {
			?>
				<h1><?php echo sprintf(__('Add a new node under %s'), $parent->title); ?></h1>
			<?php
			} else {
				?>
				<h1><?php echo sprintf(__('Edit the node %s'), $node->title); ?></h1>
				<?php
			}
		} else {
			echo '<h1>' . sprintf(__('Content wizard, step %d of %d'), 2, 4) . '</h1>';	
		}
		?>
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
				<?php
				if (!$easy) {
				?>
				<tr>
					<td>
						<strong><?php echo __('Type') ?>:</strong><br />
						<?php echo __('Specify the type of the node'); ?>
					</td>
					<td>
						<select name="type"<?php echo ($do == 'edit') ? ' disabled="disabled"' : '' ?>>
							<option name="">--<?php echo __('Select') ?>--</option>
							<?php foreach($type_options as $type => $extension) {
								$ext = utils::load_extension($extension['extension']);
								$show = true;
								if (method_exists($ext, $type . '_allow_as_child')) {
									$function = $type . '_allow_as_child';
									$show = $ext->$function($parent);
								}
								
								if (!$show) {
									continue;
								}
								
								?><option value="<?php echo $type; ?>"<?php echo ( $node->type == $type ? ' selected="selected"' : '') ?>><?php echo $type; ?></option><?php
							} ?></select>
					</td>
				</tr>
				<?php
				} else {
					$type = explode('::', base64_decode($_POST['type']));
					$type = $type[1];
					?>
					<input type="hidden" name="type" value="<?php echo $type ?>" />
					<input type="hidden" name="easy" value="true" />
					<?php	
				}
				?>
				<tr>
					<td>
						<strong> <?php echo __('Title'); ?></strong><br />
						<?php echo __('Enter the title for the node. This title will be automatically cleaned.') ?>
					</td>
					<td>
						<input type="text" name="title" id="title" value="<?php echo $node->title ?>" />
					</td>
				</tr>
				<tr>
					<td>
						<strong> <?php echo __('Clean Title'); ?></strong><br />
						<?php echo __('The clean title for the node. If empty, this will be automatically generated. Also, when changing the title, this will also be generated.') ?>
					</td>
					<td>
						<input type="text" name="title_clean" id="title_clean" value="<?php echo $node->title_clean ?>" />
					</td>
				</tr>
				<?php /*
				<tr>
					<td>
						<strong><?php echo __('Parent dir'); ?></strong><br />
						<?php echo __('The parent dir for the node. This is not required.') ?><br />
						<a href="#" onclick="showParentDirExample();"><?php echo __("Show example") ?></a>
					</td>
					
					<td>
						<input type="text" name="parentdir" value="<?php echo $node->parentdir ?>" />
					</td>
				</tr>
				*/ ?>
				<tr>
					<td>
						<strong><?php echo __('Extension'); ?></strong><br />
						<?php echo __('Enter the extension. By example, html. Don\'t put a dot (\'.\') at the begin of the extension!.') ?><br />
					</td>
					
					<td>
						<input type="text" name="extension" value="<?php echo $node->extension ?>" />
					</td>
				</tr>
					
				<tr>
					<td>
						<strong><?php echo __('Description'); ?></strong><br />
						<?php echo __('Enter the description for the node') ?>
					</td>
					<td>
						<textarea rows="3" cols="28" name="description"><?php echo $node->description ?></textarea>
					</td>
				</tr>
								
				<tr>
					<td colspan="2">
						<input type="hidden" name="node_id" value="<?php echo $node_id; ?>" />
						<input type="hidden" name="do" value="<?php echo $do ?>" />
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