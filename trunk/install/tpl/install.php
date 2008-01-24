<html>

<head>
<title><?php echo __('Installation wizard') ?> / <?php echo __('Step') . ' ' . $step ?></title>
<link rel="stylesheet" href="install.css" type="text/css" />
</head>

<body>
<form action="index.php" method="post">
<table align="center" width="600" cellpadding="0" cellspacing="0">
<tr><th><?php echo __('viennaCMS installation wizard!') ?> - <?php echo $stepname ?></th></tr>

<tr><td class="row3"><?php echo sprintf(__('Step %s of %s'), $step, $total_step); ?></td></tr>

<tr><td class="row2">
<?php if (isset($ierr)) : ?>
	<?php echo $ierr ?><br />
<?php endif; ?>
<input type="hidden" name="step" value="<?php echo $step + 1 ?>" />
<?php if ($step == 1) : ?>
	<?php echo __('Welcome to the viennaCMS installation wizard. placeholder') ?>
	<?php echo $message ?>
	<table border="0" width="100%">
	<tr><th colspan="2" class="row1">
	<?php echo __('File rights') ?></th></tr>
				<tr>
					<td><?php echo __('File') ?></td>
					<td><?php echo __('Writable?') ?></td>
				</tr>
				<?php foreach ($files as $name => $write) { ?>
				<tr>
				<td><?php echo $name ?></td>
				<td><?php echo $write ?></td>
				</tr>
				<?php } ?>
			</table>
	<?php echo __('message about not being writable or so') ?>
<?php endif; ?>
<?php if ($step == 2) : ?>
  <table width="100%" border="0">
    <tr> 
      <th colspan="2"><?php echo __('Database login data') ?></th>
    </tr>
		<tr>
			<td>{L_DB_HOST}</td>
			<td><input type="text" value="localhost" name="host"></td>

		</tr>
		<tr>
			<td>{L_DB_UNAME}</td>
			<td><input type="text" value="" name="username"></td>
		</tr>
		<tr>
			<td>{L_DB_PASS}</td>
			<td><input type="password" value="" name="password"></td>

		</tr>
		<tr>
			<td>{L_DB_NAME}</td>
			<td><input type="text" value="" name="database"></td>
		</tr>
		<tr>
			<td><?php echo __('Table prefix') ?></td>
			<td><input type="text" value="viennacms_" name="prefix"></td>
		</tr>
		<tr><th colspan="2"><?php echo __('User information') ?>
		</th></tr>

		<tr>
			<td><?php echo __('Username') ?></td>
			<td><input type="text" value="username" name="name2"></td>
		</tr>
		<tr>
			<td><?php echo __('Password') ?></td>
			<td><input type="password" value="" name="ww2"></td>
		</tr>		
	</table>
<?php endif; ?>
<?php if ($step == 3) : ?>
	Installation_complete_message
<?php endif; ?>
<tr>
<th style="text-align:right;">
<input type="button" onclick="history.back(1);" value="&laquo; <?php echo __('Back'); ?>" />
<input type="button" onclick="location.href='index.php?step=1'" value="<?php echo __('Restart') ?>" />
<input type="submit" value="<?php echo __('Next') ?> &raquo;" name="submitIt" id="submitIt"<?php echo $disabled ?> />
</th>
</table>
</form>
</body>
</html>