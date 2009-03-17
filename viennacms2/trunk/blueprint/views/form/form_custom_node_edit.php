<?php
if ($this['form']->parameter->typedata['type'] != 'static') {
	return false;
}
?>

<div class="node-form-left">
	<div class="node-important-box">
		<?php echo $this['custom_data']['raw_fields']['title'] ?>
	</div>

	<div class="node-editor">
		<?php echo $this['custom_data']['raw_fields']['revision_content'] ?>
	</div>

	<div class="node-description pane">
		<h1><?php echo __('Description') ?></h1>
		<?php echo $this['custom_data']['raw_fields']['description'] ?>
		<p class="description"><?php echo $this['form']->form['fields']['description']['description'] ?></p>
	</div>
</div>

<div class="node-form-right">
	<?php
	foreach ($this['custom_data']['raw_groups'] as $group_id => $content) {
		if ($group_id == 'node_revision' || $group_id == 'node_details') {
			continue;
		}

		echo '<div class="pane">' . '<h1>' . $this['form']->form['groups'][$group_id]['title'] . '</h1>' . $content . '</div>';
	}
	?>

	<?php echo $this['custom_data']['raw_fields']['widgets'] ?>
</div>

<?php echo $this['form']->show_by_type('hidden', $this['custom_data']['fields']); ?>

<br style="clear: both;" />