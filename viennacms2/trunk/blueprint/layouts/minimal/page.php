<!-- $Id: page.php 328 2008-11-30 18:38:02Z bas $ -->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
	<head>
		<title><?php echo __('viennaCMS installation') ?></title>
		<?php echo $this['head'] ?>
		<?php echo $this['styles'] ?>
		<?php echo $this['scripts'] ?>
	</head>
	<body>
	<div id="wrap">
	<div id="header">
		<a href="#" class="logo"></a>
	</div>
	<div id="menucontainer">
		<div>
		<ul id="menu">
			
		</ul>
		</div>
	</div>
	<div id="content">
		<h1 id="pagetitle"><?php echo $this['title'] ?></h1>
		<br style="clear: both;" />
		<?php echo $this['content'] ?>
	</div>
	<div id="footer">
		Powered by <a href="http://www.viennacms.nl/">viennaCMS</a> &copy; 2008, 2009 viennaCMS Group
	</div>
	</div>
	</body>
</html>