<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title><?php echo $title ?></title>
		<?php echo $head ?>
		<link rel="stylesheet" href="<?php echo $stylesheet ?>" />
	</head>
	<body>
		<div class="maintext">
			<?php echo $crumbs ?>
			<br />
			<br />
			<?php echo $middle ?>
		</div>
		<div class="menu">
			<?php if ($nav_level2) : ?><div class="menu_block">
				<h1><?php echo __('Navigation') ?></h1>
				<ul>
					<?php echo $nav_level2 ?>
				</ul>
				<br />
			</div><?php endif; ?>
			<?php if ($left) : ?><?php echo $left ?><?php endif; ?>
		</div>
		<div class="header">
			<h1><?php echo $sitename ?></h1>
		</div>
		<div id="navcontainer">
			<ul id="navlist">
<?php echo $nav_level1 ?>
			</ul>
		</div>
		<?php echo $footer ?>
	</body>
</html>