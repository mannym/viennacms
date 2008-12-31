<!-- $Id$ -->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
	<head>
		<title><?php echo $this['sitename'] ?> &bull; <?php echo $this['title'] ?></title>
		<?php echo $this['head'] ?>
		<?php echo $this['styles'] ?>
		<?php echo $this['scripts'] ?>
	</head>
	<body>
	<div id="wrap">
	<div id="header">
		<a href="<?php echo $this['siteurl'] ?>" class="logo"></a><span><a href="<?php echo $this['login_logout_url'] ?>"><?php echo $this['login_logout'] ?></a><?php
		if($this['acp_auth']) { ?> &bull; <a href="<?php echo $this['acp_url']; ?>"><?php echo __('ACP'); ?></a><?php } ?></span>
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
	<div id="sidebar">
		<?php
		/**
		* @todo modify the navigation stuff to be block-based, oh, and could we then PLEASE add the real parent page title, instead of this string?
		*/
		if (!empty($this['nav'][2])) {
			echo '<h1>' . __('Sub-navigation') . '</h1>';
			echo '<ul>';
			echo $this['nav'][2];
			echo '</ul>';
		}
		?>
		<?php echo $this['main_sidebar'] ?>
	</div>
	<div id="footer">
		Powered by <a href="http://www.viennacms.nl/">viennaCMS</a> &copy; 2008, 2009 viennaCMS Group<br />
		<?php
		echo $this['debug_output'];
		?>
	</div>
	</div>
	</body>
</html>