<?php echo $this['prefix'] ?>
<h2><?php echo __('Preview') ?></h2>
<p><?php echo __('This preview shows how the file widget will look like on your web site.') ?></p>
<div style="width: 96%; max-height: 300px; overflow: auto; border: 1px dashed #0099ff; padding: 10px;">
	<?php echo $this['preview'] ?>
</div>

<h2><?php echo __('Information') ?></h2>
<?php
echo sprintf(__('%sDownloads:%s %d'), '<strong>', '</strong>', (string)$this['file']->options['downloads']) . '<br />';

echo '<a href="' . $this['delete_url'] . '" onclick="return confirm(\'' . __('Do you really want to delete this file? This can not be undone.') . '\');" class="delete">' . __('Delete this file') . '</a>';
?>