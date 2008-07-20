<?php
/**
* ACP login/logout file for viennaCMS.
* 
* @package viennaCMS
* @author viennacms.nl
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

define('IN_VIENNACMS', true);
define('IN_ADMIN', true);
include('../start.php');

$user = user::getnew();
if (isset($_GET['mode']) && $_GET['mode'] == 'logout') {
	$user->logout();
	header('Location: ' . utils::base() . 'index.php');
	exit;
} else if (isset($_POST['submit'])) {
	if($user->login($_POST['username'], $_POST['password']))
	{
		header('Location: ' . utils::base() . 'adm/index.php');
		exit;
	}
} else {
	?><html>
	<head>
		<title><?php echo __("Login") ?></title>
		<link rel="stylesheet" href="style/style.css" />
	</head>
	<body>
<div id="main-items">
</div>
<div id="tree-left">
</div>
<div id="system-right">
				<form action="" method="post">
					<table>
						<tr>
							<td><?php echo __('Username') ?>: </td>
							<td><input type="text" name="username" /></td>
						</tr>
						<tr>
							<td><?php echo __('Password') ?>: </td>
							<td><input type="password" name="password" /></td>
						</tr>
						<tr>
							<td colspan="2"><input type="submit" name="submit" value="<?php echo __('Login') ?>" /></td>
						</tr>
					</table>
				</form>
		</div>
	</body>
</html>
	<?php
}
?>