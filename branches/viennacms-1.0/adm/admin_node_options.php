<?php
 /**
 * Page to edit or add node options
 * "O no! I want this node to be blue!"
 * 
 * @package viennaCMS
 * @author viennacms.nl
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

define('IN_VIENNACMS', true);
define('IN_ADMIN', true);
include('../start.php');
$user = user::getnew();
$user->checkacpauth();

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

$options = utils::run_hook_all('options_' . $node->type, $node->options);
if(in_array($node->type, array('site', 'page', 'newsfolder'))) {
	$options = array_merge($options, array(
		'template' => array(
				'type'			=> 'template',
				'name'			=> 'template',
				'title' 		=> __('Template'),
 	            'description'	=> __('The template that will be used for this node, and child nodes. Leave empty to use the parent\'s template.'),
				'value'			=> $node->options['template']
		)
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
		$form = utils::load_extension('form');
		$form->action = '?mode=save';
		$form->submit = __('Save');
		$form->setformfields($options);
		$form->_add_formfield(array(
			'type'			=> 'hidden',
			'name'			=> 'node',
			'value'			=> $node->node_id
		));
		if ($easy) {
			$form->_add_formfield(array(
				'type'			=> 'hidden',
				'name'			=> 'easy',
				'value'			=> 'true'
			));
		}
		$form->title = __('Options for this node');
		$form->generateform();
		echo $form->content;
		include('./footer.php');
	break;
}
?>