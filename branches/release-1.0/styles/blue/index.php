<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<title><?php echo $title ?></title>
		<?php echo $head ?>
		<link rel="stylesheet" href="<?php echo $stylesheet ?>" />
	</head>
	<body>
		<div class="content">
			<?php echo $crumbs ?>
			<br />
			<br />
			<?php echo $middle ?>
		</div>
		<div class="leftmenu">
			<?php if ($nav_level2) : ?><div class="block">
				<h1><?php echo __('Navigation') ?></h1>
				<ul>
					<?php echo $nav_level2 ?>
				</ul>
			</div>
			<br /><?php endif; ?><?php if ($nav_level3) : ?>
			<div class="block">
				<h1><?php echo __('Navigation') ?></h1>
				<ul>
					<?php echo $nav_level3 ?>
				</ul>
			</div>
			<br /><?php endif; ?><?php if ($left) : ?>
			<?php echo $left ?><?php endif; ?>
		</div>
		<div class="header">		
			<div style="text-align: right; text-decoration: underline;"><a href="adm/" style="color: black;"><?php echo __('ACP'); ?></a></div><br />
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