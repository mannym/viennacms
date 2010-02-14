<?php
for ($i = 0; $i <= 1; $i++) {
$header = ($i == 0) ? __('Installed extensions') : __('Disabled extensions');
	
echo '<h1>' . $header . '</h1>';
	
$values = array();

if ($i == 0) {
	$values = $this['installed'];
} else if ($i == 1) {
	$values = $this['disabled'];
}

if (empty($values)) {
	if ($i == 0) {
		echo __('No extensions are installed.');
	} else if ($i == 1) {
		echo __('There are no disabled extensions.');
	}
	
	continue;
}
?>
<table class="extensions<?php echo ($i == 0) ? ' activated' : '' ?>">
	<thead>
	<tr>
		<th style="width: 15%;">
			<?php echo __('Extension') ?>
		</th>
		<th style="width: 8%">
			<?php echo __('Version') ?>
		</th>
		<th>
			<?php echo __('Description') ?>
		</th>
		<th style="width: 9%">
			<?php echo __('Actions') ?>
		</th>
	</tr>
	</thead>
	<?php
	foreach ($values as $extension) {
		?>
		<tr>
			<td>
				<a href="<?php echo $extension['url'] ?>"><?php echo $extension['name'] ?></a>
			</td>
			<td>
				<?php echo $extension['version'] ?>
			</td>
			<td>
				<?php echo $extension['description'] ?>
				<em><?php echo sprintf(__('By %s.'), '<a href="' . $extension['author_url'] . '">' . $extension['author'] . '</a>') ?></em>
			</td>
			<td>
				<?php echo $extension['actions'] ?>
			</td>
		</tr>
		<?php
	}
	?>
</table>
<?php
}
?>