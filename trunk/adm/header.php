<?php
if(!defined('IN_VIENNACMS')) {
		die('Hacking attempt!');
}
/*$query_string 	= preg_replace('#(&|\?)tab=(.*)#', '', $_SERVER['query_string']);
$query_string	= (!empty($query_string)) ? ('?' . $query_string . '&amp;tab=') : ('?tab='); 
$h_general		= $query_string . 'general';
$h_node	 		= $query_string . 'node';*/
?>
<html>
	<head>
		<title><?php echo $page_title ?></title>
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
				height: 120px;
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
			}
			
			#right {
				float: right;
				width: 74%;
			}
			
			table {
				font-size: 12px;
			}
			
			.treeview ul { background-color: white; }
			
			.treeview, .treeview ul { 
				padding: 0;
				margin: 0;
				list-style: none;
			}

			.treeview div.hitarea {
				height: 15px;
				width: 15px;
				margin-left: -15px;
				float: left;
				cursor: pointer;
			}
			/* fix for IE6 */
			* html div.hitarea {
				background: #fff;
				filter: alpha(opacity=0);
				display: inline;
				float:none;
			}

			.treeview li { 
				margin: 0;
				padding: 3px 0pt 3px 16px;
			}
			
			.treeview a.selected {
				background-color: #eee;
			}

			#treecontrol { margin: 1em 0; }

			.treeview .hover { color: red; cursor: pointer; }
	
			.treeview li { background: url(images/tv-item.gif) 0 0 no-repeat; }
			.treeview .collapsable { background-image: url(images/tv-collapsable.gif); }
			.treeview .expandable { background-image: url(images/tv-expandable.gif); }
			.treeview .last { background-image: url(images/tv-item-last.gif); }
			.treeview .lastCollapsable { background-image: url(images/tv-collapsable-last.gif); }
			.treeview .lastExpandable { background-image: url(images/tv-expandable-last.gif); }
		
			.nodes {
				margin-top: 15px;
				margin-left: 10px;
			}
	
			.nodes a { padding-left: 19px; display: block; height: 16px; text-decoration: none; color: #0066ff; }
			.nodes a:visited { color: #0066ff; }
			.nodes a.site { background: url(images/site.png) 0 0 no-repeat; }
			.nodes a.page { background: url(images/page.png) 0 0 no-repeat; }
			.nodes .selected > a {
				text-decoration: underline;
				background-color: #eee;
			}
	
			p.icon_p {
				clear: left;
			}
			img.icon, p.icon_p img {
				height: 48px;
				width: 48px;
				float: left;
				margin: 5px 10px 5px 10px;
				border: 0;
			}
			#navcontainer {
				left: 10px;
				border: 1px solid #0044ff;
				border-top: none;
				border-right: none;
				border-bottom: none;
			}

			#navlist {
				list-style-type: none;
				margin: 0px;
				padding: 0px;
			}

			#navlist li {
				display: inline;
			}

			#navlist li a {
				float: left;
				padding-right: 3px;
				padding-left: 3px;
				color: #000000;
				font-size: 17px;
				height: 18px !important;
				height: 23px;
				padding-bottom: 5px;
				border-right: 1px solid #0044ff;
				border-top: 1px solid #0044ff;
				background-color: #0055ff;
			}

			#navlist li.active a {
				background-color: #0066ff;
				border-top: 1px solid #0055ff;
			}

			#navlist li a:hover {
				color: #444444;
			}
		</style>	
		<script src="js/jquery.js" type="text/javascript"></script>
		<script src="js/jquery.cookie.js" type="text/javascript"></script>
		<script src="js/jquery.treeview.js" type="text/javascript"></script>
		<script src="js/selectnode.js" type="text/javascript"></script>
		<script type="text/javascript">
			$(document).ready(function() {
				$(".nodes").treeview({
				<?php
				if (!defined('LIGHT_ADMIN')) {
				?>
				persist: "location",
				<?php
				} else {
				?>
				persist: "all",
				<?php } ?>
				collapsed: true,
				unique: true
				});
			});
		</script>
		<?php
		if (defined('LIGHT_ADMIN')) { return; }
		?>
		<script language="javascript" type="text/javascript" src="../includes/js/tinymce/tiny_mce.js"></script>
<script language="javascript" type="text/javascript">
	tinyMCE.init({
		mode : "textareas",
		theme : "advanced",
		editor_selector : "wysiwyg",
		plugins : "nodelink",
		theme_advanced_buttons3_add_before : "nodelink"
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
						<?php /*<li>
							<a href="<?php echo $h_general ?>"><?php echo __("General"); ?></a>
						</li>
						<li>
							<a href="<?php echo $h_node; ?>"><?php echo __('Nodes'); ?></a>
						</li>*/?>
						<li>
							<a href="admin_files.php"><?php echo __("Files"); ?></a>
						</li>
					</ul>
				</div>
			</div>
			<div id="left">
				<ul class="nodes">
					<?php
					if (isset($display_admin_tree) && $display_admin_tree) {
						//FIXME: add display_admin_tree value in query string.  
						utils::get_admin_tree();
					}
					
					$display_tree_msg = '<p><a href="' . $_SERVER['PHP_SELF'] . '?' . preg_replace('#(&)?(display_admin_tree\=)(1|0)#', '', $_SERVER['QUERY_STRING']) . (isset($_GET['node']) ? '&amp;' : '') . 'display_admin_tree=' . ($display_admin_tree ? 1 : 0) .'">' . ($display_admin_tree ? __('Hide the admin node tree') : __('Display the admin node tree')) . '</a></p>';
					echo $display_tree_msg;
					?>
				</ul>
			</div>
			<div id="right">