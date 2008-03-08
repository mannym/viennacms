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
$easy = (isset($_POST['easy']) || isset($_GET['easy']));
if(!isset($_GET['node']) && !isset($_POST['node'])) {
	header('Location: ' . utils::base() . 'index.php');
	exit;
}
$node_id = (isset($_GET['node'])) ? intval($_GET['node']) : intval($_POST['node']);
$node = new CMS_Node();
$node->node_id = $node_id;
$node->read();

$options = utils::run_hook_all('options_' . $node->type);
if(in_array($node->type, array('site', 'newsfolder', 'page', 'news'))) {
		$options = array_merge($options, array(
		'template' => array(
			'title' => __('Template'),
			'description' => __('The template that will be used for this node, and child nodes. Leave empty to use the parent\'s template.')
		),
	));
}


$page_title = __('viennaCMS ACP - Node options');

switch($mode) {
	case 'save':
		error_reporting(E_ALL);
		foreach ($_POST as $key => $value) {
			if($value == '--' . __('Select') . '--')
			{
				$value = '';
			}
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
		
		utils::get_types();
		
		if (!$easy) {
			header('Location: ' . utils::base() . 'admin_node.php?node=' . $node->node_id);
		} else {
			if (utils::$types[$node->type]['type'] == NODE_MODULES) {
				header('Location: ' . utils::base() . 'admin_node_modules.php?easy=true&node=' . $node->node_id);
			} else if (utils::$types[$node->type]['type'] == NODE_CONTENT) {
				header('Location: ' . utils::base() . 'admin_node_content.php?easy=true&node=' . $node->node_id);
			} else {
				header('Location: ' . utils::base() . 'admin_node.php?node=' . $node->node_id);
			}
		}
		exit;
	break;
	case 'form':
	default:
		include('./header.php');
		if (!$easy) {
		?>
		<h1><?php echo sprintf(__('Edit options for %s'), $node->title); ?></h1>
		<?php
		} else {
			echo '<h1>' . sprintf(__('Content wizard, step %d of %d'), 3, 4) . '</h1>';	
		}
		?>
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
							<?php  if($key != 'template' && $key != 'language') {
								?><input type="text" name="<?php echo $key ?>" value="<?php echo $node->options[$key] ?>" /><?php
							}
							elseif($key == 'template') { ?>
							<select name="template">
								<option name="">--<?php echo __('Select') ?>--</option>
								<?php 
								$templates = scandir(ROOT_PATH . 'styles');
								foreach($templates as $template) {
									if(is_dir(ROOT_PATH . 'styles/' . $template) && file_exists(ROOT_PATH . 'styles/' . $template . '/index.php') && file_exists(ROOT_PATH . 'styles/' . $template . '/module.php'))
									{
										?><option name="<?php echo $template ?>"><?php echo $template ?></option>
								<?php } 
								}
								?>
							</select>
							<?php } 
							elseif($key == 'language') { ?>
							<select name="language">
								<option name="">--<?php echo __('Select') ?>--</option>
								<?php 
								$languages = scandir(ROOT_PATH . 'locale');
								foreach($languages as $language) {
									if(is_dir(ROOT_PATH . 'locale/' . $language) && is_dir(ROOT_PATH . 'locale/' . $language . '/LC_MESSAGES/') && file_exists(ROOT_PATH . 'locale/' . $language . '/LC_MESSAGES/viennacms.mo'))
									{
										?><option name="<?php echo $language ?>"><?php echo $language ?></option>
								<?php } 
								}
								?>
							</select>
							<?php } ?>
						</td>
					</tr>
					<?php
				}
				?>
				<tr>
					<td colspan="2">
						<?php
						if ($easy) {
							?>
							<input type="hidden" name="easy" value="true" />
							<?php	
						}
						?>
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
?>