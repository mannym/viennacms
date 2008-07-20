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
		
		echo 'reload';
	}
}
?>