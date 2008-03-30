<?php
/**
* ACP login/logout file for viennaCMS.
* 
* @package viennaCMS
* @author viennacms.nl
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

define('IN_VIENNACMS', true);
include('../start.php');

$user = user::getnew();
if (isset($_GET['mode']) && $_GET['mode'] == 'logout') {
	$user->logout();
	header('Location: ' . utils::base() . 'index.php');
	exit;
} else if (isset($_POST['submit'])) {
	$user->login($_POST['username'], $_POST['password']);
	header('Location: ' . utils::base() . 'index.php');
	exit;
} else {
	?><html>
	<head>
		<title><?php echo __("Login") ?></title>
		<style type="text/css">
			html {
				height: 100%;
			}

			body {
 				font-family: Verdana, Arial, sans-serif;
				font-size: 10px;
				line-height: 140%;
				margin: 0px;
				text-align: center;
				background-color: #ACA8A1;
				height: 100%;
			}

			#wrap {
				width: 85%;
				text-align: left;
				margin-left: auto;
				margin-right: auto;
				font-size: 1.2em;
				background-color: #ffffff;
				border-left: 1px solid #888888;
				border-right: 1px solid #888888;
				height: 100%;
			}

			#header {
				background-color: #0066ff;
				width: 100%;
				height: 100px;
			}
			
			#header h1 {
				font-family: "Segoe UI", Arial, sans-serif;
				color: #ffffff;
				text-align: center;	
				font-size: 50px;
				font-weight: normal;
				margin: 0px;
				padding-top: 40px;
			}
			
			#left {
				float: left;
				width: 24%;
				text-align: center;
			}
			
			#right {
				float: right;
				width: 74%;
			}
			
			table {
				font-size: 12px;
				text-align: center;
				align: center;
			}
		</style>	
	</head>
	<body>
		<div id="wrap">
			<div id="header">
				<div style="text-align: right; color: black !important;"><a href="../"><?php echo __('View site') ?></a></div>
				<h1><?php echo __('viennaCMS ACP'); ?></h1>
			</div>
			<div id="left" style="text-align: center; align: center;">
				<form action="" method="post">
					<table align="center" style="text-align: center;">
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
		</div>
	</body>
</html>
	<?php
}
?>