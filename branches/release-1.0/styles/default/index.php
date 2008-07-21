<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title><?php echo $sitename ?> &bull; <?php echo $title ?></title>
		<?php echo $head ?>
		<link rel="stylesheet" href="<?php echo $stylesheet ?>" />
	</head>
	<body>
	<div id="wrap">
	<div id="header">
		<a href="<?php echo $homeurl ?>" class="logo"></a>
		<span><a href="adm/"><?php echo __('ACP') ?></a></span>
	</div>
	<div id="menucontainer">
	<div>
	<ul id="menu">
<?php echo $nav_level1 ?>
	</ul>
	</div>
	</div>
	<div id="content">
		<span class="breadcrumbs"><?php echo $crumbs ?></span>
		<h1 id="pagetitle"><?php echo $title ?></h1>
		<br style="clear: both;" />
		<?php echo $content ?>
	</div>
	<div id="sidebar">
		<?php if ($nav_level2) : ?>
			<h1><?php echo __('Navigation') ?></h1>
			<ul>
				<?php echo $nav_level2 ?>
			</ul>
			<br />
		<?php endif;?>
		
		<?php if ($nav_level3) : ?>
			<h1><?php echo __('Navigation') ?></h1>
			<ul>
				<?php echo $nav_level3 ?>
			</ul>
			<br />
		<?php endif;?>
		
		<?php if ($nav_level4) : ?>
			<h1><?php echo __('Navigation') ?></h1>
			<ul>
				<?php echo $nav_level4 ?>
			</ul>
			<br />
		<?php endif;?>
		
		<?php if ($right): echo $right; endif; ?>
	</div>
	<div id="footer">
		Powered by <a href="http://www.viennacms.nl/">viennaCMS</a> &copy; 2008 viennaCMS Group
	</div>
	</div>	
		<?php echo $footer ?>
	</body>
</html>