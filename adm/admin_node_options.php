<?php
 /**
 * Page to edit or add node options
 * "O no! I want this node to be blue!"
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
if(!isset($_GET['node']) && !isset($_POST['node'])) {
	header('Location: ' . utils::base() . 'index.php');
	exit;
}
$node_id = (isset($_GET['node'])) ? intval($_GET['node']) : intval($_POST['node']);
$node = new CMS_Node();
$node->node_id = $node_id;
$node->read();

$options = utils::run_hook_all('options_' . $node->type);
$options = array_merge($options, array(
	'template' => array(
		'title' => __('Template'),
		'description' => __('The template that will be used for this node, and child nodes. Leave empty to use the parent\'s template.')
	),
));

$page_title = __('viennaCMS ACP - Node options');

switch($mode) {
	case 'save':
		error_reporting(E_ALL);
		foreach ($_POST as $key => $value) {
			if (isset($options[$key])) {
				$sql = "SELECT * FROM " . NODE_OPTIONS_TABLE . "
				WHERE node_id = " . $node->node_id . "
				AND option_name = '" . $key . "'";
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$add = ($row === false) ? true : false;
				$node->write_option($key, $value, $add);
			}
		}
		
		header('Location: ' . utils::base() . 'admin_node.php?node=' . $node->node_id);
		exit;
	break;
	case 'form':
	default:
		include('./header.php');
		?>
		<h1><?php echo sprintf(__('Edit options for %s'), $node->title); ?></h1>
		<form action="?mode=save" method="post">
			<table>
				<?php
				foreach ($options as $key => $data) {
					?>
					<tr>
						<td width="70%">
							<strong><?php echo $data['title'] ?></strong><br />
							<?php echo $data['description'] ?>
						</td>
						<td width="30%">
							<input type="text" name="<?php echo $key ?>" value="<?php echo $node->options[$key] ?>" />
						</td>
					</tr>
					<?php
				}
				?>
			<tr>
				<td colspan="2">
					<input type="hidden" name="node" value="<?php echo $node->node_id ?>" />
					<input type="submit" value="<?php echo __('Save') ?>" />
				</td>
			</tr>
			</table>
		</form>
		<?php
		include('./footer.php');
	break;
}