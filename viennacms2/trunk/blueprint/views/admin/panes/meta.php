<?php
if ($this['error'] != '') {
	echo $this['error'];
	return;
}
?>
<ul class="treeview">
	<?php echo $this['output'] ?>
</ul>