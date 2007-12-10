<?php
define('IN_viennaCMS', true);
include('../start.php');
$user = user::getnew();
$user->checkacpauth();

switch ($_GET['mode']) {
	case 'cleantitle':
		$clean = utils::clean_title($_GET['title']);
		echo $clean;
		exit;
	break;
}
?>