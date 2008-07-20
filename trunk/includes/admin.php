<?php
class admin {
	static function do_action($action) {
		self::$action();
	}
	
	static function go_call() {
		$callback = explode('::', $_GET['callback']);
		$args = unserialize(base64_decode($_GET['params']));
		
		$ext = utils::load_extension($callback[0]);
		$ext->{$callback[1]}($args);
	}
	
	static function get_main() {
		$items = utils::run_hook_all('admin_get_mainitems');
		foreach ($items as $id => $item) {
			?>
			<a href="#" onclick="load_main_option('<?php echo $id ?>'); return false;">
				<img src="../<?php echo $item['image'] ?>" alt="<?php echo $item['title'] ?>" />
				<span><?php echo $item['title'] ?></span>
			</a>	
			<?php
		}
	}
	
	static function get_callback($cb, $params) {
		return 'index.php?action=go_call&params=' . base64_encode(serialize($params))  . '&callback=' . implode('::', $cb);
	}
	
	static function show_actions() {
		$items = utils::run_hook_all('admin_get_actions', $_GET['id']);
		// first the categories
		$i = 0;
		$count = count($items);
		
		// first check if any are empty
		foreach ($items as $id => $item) {
			if (empty($item['data'])) {
				$count--;
			}
		}
		
		foreach ($items as $id => $item) {
			if (($count % 2) && (($count - $i) == 1)) {
				$class = 'full';	
			} else {
				$class = 'half';
			}
			
			if (empty($item['data'])) {
				continue;
			}
			
			$i++;
			?> 
			<div class="group <?php echo $class ?>">
			<h1><img src="../<?php echo $item['image'] ?>" alt="<?php echo $item['title'] ?>" /><span><?php echo $item['title'] ?></span></h1>
			<?php
			foreach ($item['data'] as $aid => $action) {
				?>
				<p class="icon_p">
				<a href="<?php echo self::get_callback($action['callback'], $action['params']) ?>">
				<img src="../<?php echo $action['image'] ?>" /><br /><?php echo $action['title'] ?></a><br /><?php echo $action['description'] ?></p>
				<?php
			}
			?>
			</ul>
			</div>
			<?php
		}
	}
	
	static function get_left() {
		$items = utils::run_hook_all('admin_get_mainitems');
		$extension = $items[$_GET['id']]['extension'];
		$ext = utils::load_extension($extension);
		
		$function = 'admin_left_' . $_GET['id'];
		$ext->$function();
	}

	static function get_default() {
		$items = utils::run_hook_all('admin_get_default');
		$extension = $items[$_GET['id']]['extension'];
		$ext = utils::load_extension($extension);
		
		$function = 'admin_default_' . $_GET['id'];
		if(is_callable(array($ext, $function)))
		{
			$ext->$function();
			return true;
		}
		echo $_GET['id'];
		return;
	}
	
	static function load() {
		global $Header;
		
		if (isset($_GET['action'])) {
			self::do_action($_GET['action']);
			exit;
		}
		
		?> 
<html>
<head>
	<title><?php echo __('viennaCMS ACP') ?></title>
	<link rel="stylesheet" href="style/style.css" />
	<script type="text/javascript" src="js/jquery.js"></script>
	<script type="text/javascript" src="js/jquery.cookie.js"></script>
	<script type="text/javascript" src="js/jquery.form.js"></script>
	<script type="text/javascript" src="js/jquery.treeview.js"></script>
	<script type="text/javascript" src="js/admin.js"></script>
	<script type="text/javascript" src="js/selectnode.js"></script>
		<script language="javascript" type="text/javascript" src="../includes/js/tinymce/tiny_mce.js"></script>
<script language="javascript" type="text/javascript">
	tinyMCE.init({
		mode : "textareas",
		theme : "advanced",
		editor_selector : "wysiwyg",
		plugins : "nodelink,viennafiles",
		theme_advanced_buttons3_add_before : "nodelink,viennafiles"
	});
</script>
	<?php echo $Header; ?>
</head>

<body>
<div id="main-items">
</div>
<div id="tree-left">
</div>
<div id="system-right">
</div>
</body>
</html>
		
		<?php
		exit;
	}
}
?>