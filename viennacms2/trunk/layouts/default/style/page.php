<html>
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
		Powered by <a href="http://www.viennacms.nl/">viennaCMS</a> &copy; 2007, 2008 viennaCMS Group
	</div>
	</div>
	</body>
</html>