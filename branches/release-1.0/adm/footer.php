<?php
if (isset($_GET['ajax'])) {
	return;
}
if (!defined('LIGHT_ADMIN')) {
?>
</div>
<br style="clear: both;" />
<div style="text-align: center;">
	Powered by viennaCMS <?php include(ROOT_PATH . 'includes/version.php'); echo $version; ?>
</div>
</div>
<?php
}
?>
</body>
</html>