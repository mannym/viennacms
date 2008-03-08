<?php
/**
 * Edit node content
 *  
 * @package viennaCMS
 * @author viennainfo.nl
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

define('IN_VIENNACMS', true);
include('../start.php');
$user = user::getnew();
$user->checkacpauth();

$mode = isset($_GET['mode']) ? $_GET['mode'] : 'form';
$node = new CMS_Node();
$node->node_id = $_REQUEST['node'];
$node->read();

utils::get_types();

switch($mode) {
	case 'save':
		$node->revision->node_content = $_POST['node_content'];
		$node->write();
		header('Location: ' . utils::base() . 'admin_node.php?node=' . $node->node_id);		
	break;
		
	default:
	case 'form':
		include('./header.php');
		?>
		<h1><?php echo sprintf(__('Edit the node content of %s'), $node->title); ?></h1>
		<form action="?mode=save" method="post">
			<?php
			$key = 'node_content';
			$val = $node->revision->node_content;
			
			switch (utils::$types[$node->type]['field']) {
				case 'wysiwyg':
				?>
				<textarea class="wysiwyg" name="<?php echo $key ?>" style="width: 500px; height: 250px;"><?php echo stripslashes(preg_replace("#\<br \/\>#", '', $val)); ?></textarea>
				<?php
				break;
				case 'textarea':
				default:
					?>
					<textarea name="<?php echo $key ?>" rows="5" cols="40"><?php echo stripslashes(preg_replace('#\<br \/\>#', '', $val)); ?></textarea>
					<?php				
				break;
			}
			?><br />
			<input type="hidden" name="node" value="<?php echo $node->node_id ?>" />
			<input type="submit" value="<?php echo __('Save') ?>" />
		</form>
		
		<?php include('./footer.php');
	break;
}		
?>