<table class="extensions" style="width: 100%; margin-top: 5px;">
	<thead>
	<tr>
		<th style="width: 15%;">
			<?php echo __('Language') ?>
		</th>
		<th>
			<?php echo __('Localized title') ?>
		</th>
		<th>
			<?php echo __('Status') ?>
		</th>
	</tr>
	</thead>
	<?php
	foreach ($this['translations'] as $item) {
		?>
		<tr class="logitem log-<?php echo ($item->translation_updated) ? 'ok' : 'error' ?>">
			<td>
				<a href="<?php echo view::url('admin/controller/translate/edit/' . $item->node_id) ?>" title="<?php echo __('Edit this translation') ?>">
					<?php echo (string)$item->options['tset_language'] ?>
				</a>
			</td>
			<td>
				<?php echo $item->title ?>
			</td>
			<td>
				<?php
				echo ($item->translation_updated) ? __('Up to date') : __('Outdated');
				?>
			</td>
		</tr>
		<?php
	}
	?>
	<tr class="logitem log-warn">
		<td colspan="3">
			<form action="<?php echo view::url('admin/controller/translate/create/' . $this['tset']->node_id) ?>" method="post">
				<strong><?php echo __('Create new translation')?>: </strong><input type="text" name="language" /><input type="submit" value="<?php echo __('Create') ?>" />
			</form>
		</td>
	</tr>
</table>