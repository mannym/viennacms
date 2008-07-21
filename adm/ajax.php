<?php
define('IN_VIENNACMS', true);
include('../start.php');
include(ROOT_PATH . 'includes/admin.php');
$user = user::getnew();
$user->checkacpauth();

switch ($_REQUEST['mode']) {
	case 'cleantitle':
		$clean = utils::clean_title($_GET['title']);
		echo $clean;
		exit;
	break;
	case 'move_node':
		$node_id = str_replace('node-', '', $_POST['id']);
		$node_id = intval($node_id);
		
		$node = new CMS_Node();
		$node->node_id = $node_id;
		$node->read();
		$nodes = $node->get_siblings_all();
		$ids = array();
		$nodes2 = array();
		foreach ($nodes as $node2) {
			$ids[] = $node2->node_id;
			$nodes2[$node2->node_id] = $node2;
		}
		$nodes = $nodes2;
		$ids = utils::array_move_element($ids, $node->node_id, $_POST['type']);
		foreach ($ids as $i => $id) {
			$nodes[$id]->node_order = $i;
			$nodes[$id]->write(false);
			//echo $id . '=>' . $i;
		}
		//var_dump($ids);
		/*foreach ($nodes as $key => $value) {
			$value->node_order = $key;
			$value->write(false);
		}
		*/
		$_GET['id'] = 'site_structure';
		$core = utils::load_extension('core');
		$core->admin_left_site_structure();
		//echo utils::get_admin_tree();
	break;
}
?>