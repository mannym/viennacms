<!-- $Id$ -->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
	<head>
		<title><?php echo $this['title'] ?></title>
		<?php echo $this['head'] ?>
		<?php echo $this['styles'] ?>
		<?php echo $this['scripts'] ?>
	</head>
	<body>
	<div id="wrap">
	<div id="header">
		<a href="" class="logo"></a>
		<span><a href="<?php echo $this['login_logout_url'] ?>"><?php echo $this['login_logout'] ?></a></span>
	</div>
	<div id="menucontainer">
	<div>
	<ul id="menu">
		<?php echo $this['nav'][1] ?>
	</ul>
	</div>
	</div>
	<div id="content">
		<h1 id="pagetitle"><?php echo $this['title'] ?></h1>
		<br style="clear: both;" />
		<?php echo $this['content'] ?>
	</div>
	<div id="footer">
		Powered by <a href="http://www.viennacms.nl/">viennaCMS</a> &copy; 2008 viennaCMS Group | 
		<?php
		echo $this->global['db']->num_queries['total'] . ' queries';
		?>
	</div>
	</div>
	</body>
</html>