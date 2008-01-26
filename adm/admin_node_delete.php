<?php
 /**
 * Delete a node
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
$mode = isset($_GET['mode']) ? $_GET['mode'] : 'confirm';
$node_id = isset($_GET['node']) ? $_GET['node'] : $_POST['node_id'];
$page_title = __("viennaCMS ACP - Delete a node");

switch($mode) {
	case 'confirmed':
		$post_vars = array('node_id');
		foreach($post_vars as $postvar) {
			if(empty($_POST[$postvar])) {
				trigger_error(__($postvar . '  not given!'), E_USER_ERROR);
				return;
			}
			$var = 'newnode_' . $postvar;
			$$var = $db->sql_escape($_POST[$postvar]); 
		}
		// Get the number of sites
		$sql = "SELECT COUNT(type) AS site_count
				FROM " . NODES_TABLE . "
				WHERE type = 'site'";
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$site_count = $row['site_count'];
		if($site_count < 1 || $site_count == 1) {
			define('SITE_DELETE_LEGAL', false);
		}
		else {
			define('SITE_DELETE_LEGAL', true);
		}
		$node = CMS_Node::getnew();
		$node->node_id = $node_id;
		$node->read();		
		if($node->type == 'site' && !SITE_DELETE_LEGAL) {
			trigger_error(__("Can't delete the node. Are you trying to delete a site?"), E_USER_ERROR);
			define('ERROR', true);
		}
		else {
			delete_node($node_id);
		}

		if(!defined('ERROR')) {
			header('Location: ' . utils::base() . 'index.php');		
		}
	break;
		
	default:
	case 'confirm':
		include('./header.php');
		?>
		<form action="?mode=confirmed" method="post">
			<div style="color: red;"><?php echo __("Are you sure you want to delete this node? This will remove any data and children."); ?></div>
			<input type="hidden" name="node_id" value="<?php echo $node_id; ?>" />
			<input type="submit" name="submit" value="<?php echo __("Submit"); ?>" />
		</form>
		<?php include('./footer.php');
	break;
}

function delete_node($node_id) {
	global $db;
	$sql = 'DELETE FROM ' . NODES_TABLE . "
			WHERE node_id = $node_id";
	if(!$db->sql_query($sql)) {
		return false;
	}
	$sql = 'DELETE FROM ' . NODE_REVISIONS_TABLE . "
			WHERE node_id = $node_id";
	if(!$db->sql_query($sql)) {
		return false;
	}
	$sql = 'SELECT node_id FROM ' . NODES_TABLE . "
			WHERE parent_id = $node_id";
	if(!$result = $db->sql_query($sql)) {
		return false;
	}
	$affected_rows = $db->sql_affectedrows();
	if($affected_rows < 1) {
		$rowset = $db->sql_fetchrowset($result);
		foreach($rowset as $row) {
			delete_node($row['node_id']);
		}
	}
	elseif($affected_rows == 1) {
		$row = $db->sql_fetchrow($result);
		delete_node($row['node_id']);
	}
		
	return true;
}
?>