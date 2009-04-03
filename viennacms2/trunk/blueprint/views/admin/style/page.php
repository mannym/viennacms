<html>
	<head>
		<base href="<?php echo manager::base() ?>" />
		<link rel="stylesheet" href="framework/js/thickbox.css" />
		<link rel="stylesheet" href="blueprint/views/admin/style.css" />
		<link rel="stylesheet" href="framework/views/system/form.css" />
		<script type="text/javascript" src="framework/js/jquery.js"></script>
		<script type="text/javascript" src="framework/js/jquery.ui.js"></script>
		<script type="text/javascript" src="framework/js/jquery.treeview.js"></script>
		<script type="text/javascript" src="framework/js/jquery.cookie.js"></script>
		<script type="text/javascript" src="framework/js/jquery.form.js"></script>
		<script type="text/javascript" src="framework/js/jquery.upload.js"></script>
		<script type="text/javascript" src="framework/js/thickbox.js"></script>
		<script type="text/javascript" src="framework/js/wymeditor/jquery.wymeditor.js"></script>
		<script type="text/javascript" src="framework/js/viennacms-acp.js"></script>
		<script type="text/javascript">
			var pane_url = '<?php echo $this['pane_url'] ?>';
		</script>
		<?php echo $this['scripts'] ?>
		<style type="text/css">
			<?php
			foreach ($this['icons'] as $key => $icon) {
				?>
				.treeview a.<?php echo $key ?>, .types a.<?php echo $key ?> { background-image: url(<?php echo $icon ?>); background-repeat: no-repeat; }
				<?php
			}
			?>
		</style>
	</head>
	<body>
		<div id="main-menu">
			<ul>
				<?php
				if ($this['views']) {
					foreach ($this['views'] as $id => $view) {
						?>
						<li><a href="<?php echo $this->url('admin/view/' . $id) ?>"><?php echo $view ?></a></li>
						<?php
					}
				}
				?>
			</ul>
		</div>
		<div id="main-content">
			<?php echo $this['toolbars'] ?>
			<?php echo $this['content'] ?>
		</div>
		<?php
		if (!empty($this['panes']['left'])) {
		?>
		<div id="panes-left" class="panes">
			<?php
			foreach ($this['panes']['left'] as $pane) {
			?>
				<div class="pane">
					<h1><?php echo $pane['title'] ?></h1>
					<div class="content">
						<?php echo $pane['content'] ?>
					</div>
				</div>
			<?php
			}
			?>
		</div>
		<?php
		}
		?>
		<?php
		if (!empty($this['panes']['right'])) {
		?>
		<div id="panes-right" class="panes">
			<?php
			foreach ($this['panes']['right'] as $pane) {
			?>
				<div class="pane">
					<h1><?php echo $pane['title'] ?></h1>
					<div class="content">
						<?php echo $pane['content'] ?>
					</div>
				</div>
			<?php
			}
			?>
		</div>
		<?php
		}
		if (!empty($this['panes']['bottom'])) {
		?>
		<div id="panes-bottom" class="panes">
			<?php
			foreach ($this['panes']['bottom'] as $pane) {
			?>
				<div class="pane">
					<h1><?php echo $pane['title'] ?></h1>
					<div class="content">
						<?php echo $pane['content'] ?>
					</div>
				</div>
			<?php
			}
			?>
		</div>
		<?php
		}
		?>
	</body>
</html>