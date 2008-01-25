<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title><?php echo $title; ?></title>
		<?php echo $head; ?>
		<link rel="stylesheet" href="<?php echo $stylesheet ?>" />
	</head>

	<body>
		<div id="wrap">
			<div id="header">
				<a href="" class="logo"></a>
				<span><a href="adm/"><?php echo __('ACP'); ?></a></span>
			</div>
			
			<div id="menucontainer">
				<div>
					<ul id="menu">
						<?php echo $nav_level1 ?>
					</ul>
				</div>
			</div>
			
			<div id="content">
				<span class="breadcrumbs"><?php echo $crumbs; ?></span>
				<?php echo $middle; ?>
			</div>
			
			<div id="sidebar">
				<?php if(!empty($nav_level2)): ?><h1>Navigation</h1>
				<ul>
					<?php echo $nav_level2 ?>
				</ul><?php endif; ?>
				<?php if(!empty($right)) echo $right; ?>
			</div>
			
			<div id="footer">
				Copyright by <a href="http://www.viennainfo.nl/">viennaCMS</a>.
			</div>
		</div>
	</body>
</html>