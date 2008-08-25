<?php
$routes = array(
	'@^([a-z\-]+?)/([a-z\-]+?)/(.+)$@' => array('controller', 'action', 'params'),
	'@^([a-z\-]+?)/([a-z\-]+)@' => array('controller', 'action'),
	'@^([a-z\-]+)@' => array('controller')
);