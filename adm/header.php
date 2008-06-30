<?php
/**
 * Header of the viennaCMS admin file.
 * 
 * @package viennaCMS
 * @author viennacms.nl
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

if(!defined('IN_VIENNACMS')) {
		die();
}
if (isset($_GET['ajax'])) {
	return;
}
define('ADM_HEADER', true);
?>
<html>
	<head>
		<title><?php echo $page_title ?></title>
		<link rel="stylesheet" href="admin.css" />
		<script src="js/jquery.js" type="text/javascript"></script>
		<script src="js/jquery.cookie.js" type="text/javascript"></script>
		<script src="js/jquery.treeview.js" type="text/javascript"></script>
		<script src="js/selectnode.js" type="text/javascript"></script>
			<?php
			echo $Header;
			?>
		<?php
		if (defined('LIGHT_ADMIN')) { return; }
		?>
		<script type="text/javascript">
			function updateNodeLinks() {
				$('#tree li a').after(' <a href="#" class="nudl" style="display: inline; padding: 0px; margin-right: 3px;" onclick="upMyNode(this.parentNode.id, this.parentNode.parentNode.id); return false;">^</a><a href="#" class="nudl" style="display: inline; padding: 0px;" onclick="downMyNode(this.parentNode.id, this.parentNode.parentNode.id); return false;">v</a>');
			}

			function downMyNode(id, parent) {
				$.ajax({
					cache: false,
					type: "POST",
					url: "ajax.php",
					data: "mode=move_node&type=down&id=" + id,
					success: function(output) {
						$('#tree').html(output);
						startTree('all');
						updateNodeLinks();
					}
				});
			}
			
			function upMyNode(id, parent) {
				$.ajax({
					cache: false,
					type: "POST",
					url: "ajax.php",
					data: "mode=move_node&type=up&id=" + id,
					success: function(output) {
						$('#tree').html(output);
						startTree('all');
						updateNodeLinks();
					}
				});
			}
	
			function startTree(what) {
				$(".nodes").treeview({
				<?php
				if (!defined('LIGHT_ADMIN')) {
				?>
				persist: what,
				<?php
				} else {
				?>
				persist: "allg",
				<?php } ?>
				collapsed: true,
				unique: true
				});
			}
		
			var orderOn = false;
		
			$(document).ready(function() {
				startTree('location');
				$('#left a.order').click(function() {
					if (orderOn) {
						orderOn = false;
						$('#left a.nudl').remove();
					} else {
						orderOn = true;
						updateNodeLinks();
					}
					return false;
				});
			});
		</script>
		<script language="javascript" type="text/javascript" src="../includes/js/tinymce/tiny_mce.js"></script>
<script language="javascript" type="text/javascript">
	tinyMCE.init({
		mode : "textareas",
		theme : "advanced",
		editor_selector : "wysiwyg",
		plugins : "nodelink,viennafiles",
		theme_advanced_buttons3_add_before : "nodelink,viennafiles"
	});
</script>
	</head>
	<body>
		<div id="wrap">
			<div id="header">
				<div style="text-align: right; color: black !important;"><a href="../"><?php echo __('View site') ?></a></div>
				<h1><?php echo __('viennaCMS ACP'); ?></h1><br /><br />
				<div style="clear: both;"></div>
				<div id="navcontainer">
					<ul id="navlist">
						<li>
							<a href="index.php"><?php echo __("Nodes"); ?></a>
						</li>
						<li>
							<a href="admin_files.php"><?php echo __("Files"); ?></a>
						</li>
						<li>
							<a href="admin_config.php"><?php echo __("Configuration"); ?></a>
						</li>
						<li>
							<a href="login.php?mode=logout"><?php echo __("Log out"); ?></a>
						</li>						
					</ul>
				</div>
			</div>
			<div id="left">
				<ul class="nodes" id="tree" style="display: block;">
					<?php
					if (defined('IN_FILES')) {
						$files = utils::load_extension('files');
						$files->get_admin_tree();
					}
					else if (defined('IN_CONFIG')) {
						?>
						<li><a href="admin_config.php?mode=performance" class="page"><?php echo __('Performance') ?></a></li>
						<?php
					}
					else {
						utils::get_admin_tree();
					}
					//$display_tree_msg = '<p><a href="' . $_SERVER['PHP_SELF'] . '?' . preg_replace('#(&)?(display_admin_tree\=)(1|0)#', '', $_SERVER['QUERY_STRING']) . (isset($_GET['node']) ? '&amp;' : '') . 'display_admin_tree=' . ($display_admin_tree ? 1 : 0) .'">' . ($display_admin_tree ? __('Hide the admin node tree') : __('Display the admin node tree')) . '</a></p>';
					?>
				</ul>
				<div class="nodes">
					<a href="javascript: void(0);" onClick="$('#tree').toggle(); return false;"><?php echo __('Show/hide tree') ?></a><br />
					<?php
					if (!defined('IN_FILES') && !defined('IN_CONFIG')) {
						?>
						<a href="#" class="order"><?php echo __('Move nodes') ?></a>
						<?php
					}
					?>
				</div>
			</div>
			<div id="right">