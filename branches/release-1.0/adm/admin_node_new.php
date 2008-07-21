<?php
 /**
 * Add a new node
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

ob_start();

class node_add_form extends form {
	function validate($field, $value) {
		global $db, $do, $page;
		
		if ($field == 'title_clean') {	
			if ($do == 'new') {
				$value = $db->sql_escape($value);
				$sql = 'SELECT node_id, title 
						FROM ' . NODES_TABLE . "
						WHERE title_clean = '$value';";
				$result = $db->sql_query($sql); 
				if($row = $db->sql_fetchrow($result)) {
					return __('There is already an node with the same clean title.');
				}
			}
		}
	}
	
	function submit($fields) {
		global $do, $parent, $node, $easy, $page, $cache;
		$post_vars = array('node_id', 'title', 'description', 'title_clean', 'extension');
		if ($do == 'new') {
			$post_vars[] = 'type';
		}
		foreach($post_vars as $postvar) {
			$var = 'newnode_' . $postvar;
			$$var = $fields[$postvar]; 
		}
		if ($do == 'new') {
			$node->parent_id = ($newnode_type == 'site' ? 0 : $parent->node_id);
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
		exit;
	}
}

switch($mode) {
	case 'easy':
		include('./header.php');
		$type = explode('::', base64_decode($_GET['type']));
		$ext = utils::load_extension($type[0]);
		/*if (method_exists($ext, $type[1] . '_allow_as_child')) {
			$function = $type[1] . '_allow_as_child';
			$callback = array($ext, $function);
		} else {
			$callback = false;
		}*/
		$newnode = CMS_Node::getnew();
		$newnode->type = $type[1];
		$callback = array(
			'type'	=> 'this_under_other',
			'ntype'	=> 'other',
			'node'	=> $newnode
		);
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
	case 'next':
	case 'form':
	default:
		$type_options	= utils::run_hook_all('list_types');
		$page_title		= $do == 'edit' ? __('viennaCMS ACP - Edit a node') : __('viennaCMS ACP - Add a new node');
		include('./header.php');
		if (!$easy) {
			if ($do == 'new') {
				$title = sprintf(__('Add a new node under %s'), $parent->title);
			} else {
				$title = sprintf(__('Edit the node %s'), $node->title);
			}
		} else {
			$title = sprintf(__('Content wizard, step %d of %d'), 2, 4);	
		}
		$form = new node_add_form;
		$form->elements = array(
			$title => array(

			)
		);
		$form->form_id = 'node_add_form';
		$form->action = '?mode=next';
		if (!$easy && $do != 'edit') {
			$values = array('' => array(
				'title' => '--' . __('Select') . '--',
				'selected' => false
			));
			
			foreach($type_options as $type => $extension) {
				$tempnode = new CMS_Node();
				$tempnode->type = $type;
				$ext = utils::load_extension($extension['extension']);
				$show = utils::display_allowed('this_under_other', $tempnode, $parent);
				unset($tempnode);
				/*if (method_exists($ext, $type . '_allow_as_child')) {
					$function = $type . '_allow_as_child';
					$show = $ext->$function($parent);
				}*/
				
				if (!$show) {
					continue;
				}
				$values[$type] = array(
					'title' => $type,
					'selected' => ($node->type == $type)
				);
			}
			$form->elements[$title]['type'] = array(
				'type' => 'selectbox',
				'name' => 'type',
				'title' => __('Type'),
				'description' => __('Select the type of the node'),
				'value' => $values,
				'required' => true
			);
		} else if ($easy) {
			$type = explode('::', base64_decode($_POST['type']));
			$type = $type[1];
			$form->elements[$title]['type'] = array(
				'name' => 'type',
				'type' => 'hidden',
				'value' => $type
			);
			$form->elements[$title]['easy'] = array(
				'name' => 'easy',
				'type' => 'hidden',
				'value' => 'true',
				'raw' => true
			);
		}
		$form->elements[$title]['title'] = array(
			'type'			=> 'textfield',
			'name'			=> 'title',
			'title'			=> __('Title'),
			'description'	=> __('Enter the title for the node. This title will be automatically cleaned.'),
			'value'			=> $node->title,
			'required'		=> true,
		);
		$form->elements[$title]['title_clean'] = array(
			'type'			=> 'textfield',
			'name'			=> 'title_clean',
			'title'			=> __('Clean Title'),
			'description'	=> __('The clean title for the node. When changing the title, this will be automatically generated.'),
			'value'			=> $node->title_clean,
			'required'		=> true,
		);
		$form->elements[$title]['extension'] = array(
			'type'			=> 'textfield',
			'name'			=> 'extension',
			'title'			=> __('Extension'),
			'description'	=> __('Enter the extension. By example, html. Don\'t put a dot (\'.\') at the begin of the extension!.'),
			'value'			=> $node->extension,
			'max_length'	=> 6,
		);
		$form->elements[$title]['description'] = array(
			'type'			=> 'textarea',
			'name'			=> 'description',
			'title'			=> __('Description'),
			'description'	=> __('Enter the description for the node'),
			'value'			=> $node->description,
			'required'		=> false,
		);
		$form->elements[$title]['do'] = array(
			'name' => 'do',
			'type' => 'hidden',
			'value' => $do,
			'raw' => true
		);
		$form->elements[$title]['node_id'] = array(
			'name' => 'node_id',
			'type' => 'hidden',
			'value' => $node_id,
			'raw' => true
		);
		
		$api = new formapi;
		echo $api->get_form($form);
		?>
		<script type="text/javascript">
			$('#node_add_form_title').blur(function () {
				$.get('<?php echo utils::base() ?>ajax.php?mode=cleantitle&title=' + escape($('#node_add_form_title').attr('value')), '', function(data, textStatus) {
					$('#node_add_form_title_clean').attr('value', data);
				});
			});
		</script>
		
		<?php
		include('./footer.php');
	break;
}		
?>