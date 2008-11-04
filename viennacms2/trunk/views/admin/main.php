<html>
	<head>
		<base href="<?php echo manager::base() ?>" />
		<link rel="stylesheet" href="views/admin/style.css" />
		<link rel="stylesheet" href="views/system/form.css" />
		<script type="text/javascript" src="framework/js/jquery.js"></script>
		<script type="text/javascript" src="framework/js/jquery.ui.js"></script>
		<script type="text/javascript" src="framework/js/jquery.treeview.js"></script>
		<script type="text/javascript" src="framework/js/jquery.cookie.js"></script>
		<script type="text/javascript" src="framework/js/jquery.form.js"></script>
		<script type="text/javascript" src="framework/js/wymeditor/jquery.wymeditor.js"></script>
		<script type="text/javascript" src="framework/js/viennacms-acp.js"></script>
		<script type="text/javascript">
			var pane_url = '<?php echo $this['pane_url'] ?>';
		</script>
		<style type="text/css">
			<?php
			foreach ($this['icons'] as $key => $icon) {
				?>
				.treeview a.<?php echo $key ?> { background: url(<?php echo $icon ?>) 0 0 no-repeat; }
				<?php
			}
			?>
		</style>
	</head>
	<body>
		<div id="main-menu">
		</div>
		<div id="main-content">
		</div>
		<div id="panes-left" class="panes">
			<div class="pane">
				<ul class="tabs">
					
				</ul>
				<div class="content">
				</div>
			</div>
		</div>
		<div id="panes-right" class="panes">
			<div class="pane">
				<ul class="tabs">
				</ul>
				<div class="content"></div>
			</div>
		</div>
		<div id="panes-bottom" class="panes">
			<div class="pane">
				<ul class="tabs">
				</ul>
				<div class="content"></div>
			</div>
		</div>
	</body>
</html>