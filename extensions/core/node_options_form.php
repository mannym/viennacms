<?php		
class node_options_form extends form {
	function submit($fields) {
		global $node, $easy, $db, $options;
		
		foreach ($fields as $key => $value) {
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
		
		?> 
		<script type="text/javascript">
			load_in_system('index.php?action=show_actions&id=site_structure&node=<?php echo $node->node_id ?>', 'site_structure');
		</script>
		<?php
	}
}
?>