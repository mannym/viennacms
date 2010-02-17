<?php
$updates = array(
	1 => array(
		'change' => array(),
		'add' => array('viennacms_log')
	), // from version 1 to 2
	2 => array(
		'change' => array(),
		'add' => array('viennacms_acl_entries')
	),
	3 => array(
		'change' => array(
			'add_columns' => array(
				$table_prefix . 'acl_entries' => array(
					'operation' => array('VCHAR:40', ''),
				)
			)
		),
		'add' => array()
	)
);
?>