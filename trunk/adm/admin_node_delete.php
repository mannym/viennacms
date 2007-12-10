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
		$sql = "DELETE FROM " . NODES_TABLE . " WHERE node_id = " . $node_id . " AND type <> 'site'";
		if(!$result = $db->sql_query($sql)) {
			trigger_error(__("Can't delete the node. Are you trying to delete a site?") );
		}
		$sql = "DELETE FROM " . NODES_TABLE . " WHERE parent_id = " . $node_id . " AND type <> 'site'";
		if(!$result = $db->sql_query($sql)) {
			trigger_error(__("Can't delete the node. Are you trying to delete a site?") );
		}
		header('Location: ' . utils::base() . 'index.php');		
	break;
		
	default:
	case 'confirm':
		include('./header.php');
		?>
		<form action="?mode=confirmed" method="post">
			<div style="color: red;"><?php echo __("Are you sure you want to delete this node? This will not remove any data."); ?></div>
			<input type="hidden" name="node_id" value="<?php echo $node_id; ?>" />
			<input type="submit" name="submit" value="<?php echo __("Submit"); ?>" />
		</form>
		<?php include('./footer.php');
	break;
}		
?>