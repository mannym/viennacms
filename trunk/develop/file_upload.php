<?php
define('IN_VIENNACMS', true);
include('../start.php');
utils::load_all_exts();
$mode = isset($_GET['mode']) ? $_GET['mode'] : '';
$extension_files = new extension_files();
if($mode == "save") {
	$extension_files->handle_file_upload('file');
}
else { 
?><html>
	<head>
		<title>Test</title>
	</head>
	<body>
		<?php $extension_files->upload_form(); ?>
	</body>
</html>
<?php } ?>