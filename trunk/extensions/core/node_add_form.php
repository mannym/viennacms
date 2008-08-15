<?php
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

		$newnode_parentdir = '';
		
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
			//header('Location: ' . utils::base() . 'admin_node.php?node=' . $node->node_id);
		} else {
			//header('Location: ' . utils::base() . 'admin_node_options.php?easy=true&node=' . $node->node_id);
		}
		utils::get_types();
		if (utils::$types[$node->type]['type'] == NODE_CONTENT) {
			?> 
			<script type="text/javascript">
				load_in_system('<?php echo admin::get_callback(array('core', 'admin_node_content'), array('node' => $node->node_id)) ?>', 'site_content');
			</script>
			<?php
		} else if (utils::$types[$node->type]['type'] != NODE_NO_REVISION) {
			?> 
			<script type="text/javascript">
				load_in_system('index.php?action=show_actions&id=site_content&node=<?php echo $node->node_id ?>', 'site_content');
			</script>
			<?php
		} else {
			?> 
			<script type="text/javascript">
				load_in_system('index.php?action=show_actions&id=site_structure&node=<?php echo $node->node_id ?>', 'site_structure');
			</script>
			<?php	
		}
		//echo 'reload';
		exit;
		//exit;
	}
}
?>
