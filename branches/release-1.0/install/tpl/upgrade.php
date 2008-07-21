<html>

<head>
<title><?php echo __('Installation wizard') ?> / <?php echo __('Step') . ' ' . $step ?></title>
<link rel="stylesheet" href="install.css" type="text/css" />
<script type="text/javascript">
	function langify(form) {
		var index = form.langselect.selectedIndex;
		location.href = 'upgrade.php?language=' + form.langselect.options[index].value;
	}
</script>
</head>

<body>
<form action="upgrade.php" method="post">
<table align="center" width="600" cellpadding="0" cellspacing="0">
<tr><th colspan="2"><?php echo __('viennaCMS upgrade wizard!') ?> - <?php echo $stepname ?></th></tr>

<tr><td colspan="2" class="row3"><?php echo sprintf(__('Step %s of %s'), $step, $total_step); ?></td></tr>

<tr><td colspan="2" class="row2">
<?php if (isset($ierr)) : ?>
	<?php echo $ierr ?><br />
<?php endif; ?>
<input type="hidden" name="step" value="<?php echo $step + 1 ?>" />
<?php if ($step == 1) : ?>
	<?php echo __('Welcome to the viennaCMS upgrade wizard. This wizard will upgrade your database to the current version. Click Next to start the upgrade.') ?>
<?php endif; ?>
<?php if ($step == 2) : ?>
	<?php echo $mes ?><br />
	<?php echo sprintf(__('The upgrade has completed. If there are any errors above, please visit our <a href="%s">support forums</a>.'), 'http://forum.viennainfo.nl/'); ?>
<?php endif; ?>
</td></tr>
<tr>
<th style="text-align: left;">
	<select name="langselect" onchange="langify(this.form);">
		<option value="english">Select language</option>
		<option value="english">en_US</option>
		<?php foreach ($languages as $language) { ?>
			<option value="<?php echo $language ?>"><?php echo $language ?></option>
		<?php } ?>
	</select>
</th>
<th style="text-align:right;">
<input type="button" onclick="history.back(1);" value="&laquo; <?php echo __('Back'); ?>" />
<input type="button" onclick="location.href='upgrade.php?step=1'" value="<?php echo __('Restart') ?>" />
<input type="submit" value="<?php echo __('Next') ?> &raquo;" name="submitIt" id="submitIt"<?php echo $disabled ?> />
</th>
</table>
</form>
</body>
</html>