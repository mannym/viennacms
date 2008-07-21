<html>

<head>
<title><?php echo __('Installation wizard') ?> / <?php echo __('Step') . ' ' . $step ?></title>
<link rel="stylesheet" href="install.css" type="text/css" />
<script type="text/javascript">
	function langify(form) {
		var index = form.langselect.selectedIndex;
		location.href = 'index.php?language=' + form.langselect.options[index].value;
	}
</script>
</head>

<body>
<form action="index.php" method="post">
<table align="center" width="600" cellpadding="0" cellspacing="0">
<tr><th colspan="2"><?php echo __('viennaCMS installation wizard!') ?> - <?php echo $stepname ?></th></tr>

<tr><td colspan="2" class="row3"><?php echo sprintf(__('Step %s of %s'), $step, $total_step); ?></td></tr>

<tr><td colspan="2" class="row2">
<?php if (isset($ierr)) : ?>
	<?php echo $ierr ?><br />
<?php endif; ?>
<input type="hidden" name="step" value="<?php echo $step + 1 ?>" />
<?php if ($step == 1) : ?>
	<?php echo __('Welcome to the viennaCMS installation wizard.') ?>
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
	<?php echo __('If one or more of these files or folders are not writable, you cannot install viennaCMS.') ?>
<?php endif; ?>
<?php if ($step == 2) : ?>
  <table width="100%" border="0">
    <tr> 
      <th colspan="2"><?php echo __('Database login data') ?></th>
    </tr>
    <?php
    global $error;
	if ($error) :
		global $error_msg, $dbhost, $dbuser, $dbpasswd, $dbname, $table_prefix, $admin_username;
		?>
		<tr>
			<td colspan="2"><span style="color: red;"><?php echo $error_msg ?></span></td>
		</tr>
		<?php
	endif;
    ?>
		<tr>
			<td><?php echo __('Database server (mostly localhost)') ?></td>
			<td><input type="text" value="<?php echo (empty($dbhost) ? 'localhost' : $dbhost) ?>" name="database_host" /></td>

		</tr>
		<tr>
			<td><?php echo __('Database user name') ?></td>
			<td><input type="text" value="<?php echo $dbuser ?>" name="database_username" /></td>
		</tr>
		<tr>
			<td><?php echo __('Database password') ?></td>
			<td><input type="password" value="<?php echo $dbpasswd ?>" name="database_password" /></td>

		</tr>
		<tr>
			<td><?php echo __('Database name') ?></td>
			<td><input type="text" value="<?php echo $dbname ?>" name="database_name" /></td>
		</tr>
		<tr>
			<td><?php echo __('Table prefix') ?></td>
			<td><input type="text" value="<?php echo (empty($table_prefix) ? 'viennacms_' : $table_prefix) ?>" name="table_prefix" /></td>
		</tr>
		<tr>
			<td><?php echo __('Database Type') ?></td>
			<td>
				<select name="dbms">
					<option value="mysql"><?php echo __('MySQL 5 or higher') ?></option>
					<option value="sqlite">SQLite</option>
					<!-- <option value="postgres">PostgreSQL</option> -->
				</select>
			</td>
		</tr>
		<tr><th colspan="2"><?php echo __('Admin information'); ?>
		</th></tr>

		<tr>
			<td><?php echo __('Username') ?></td>
			<td><input type="text" value="<?php echo ( empty($admin_username) ? 'admin' : $admin_username) ?>" name="admin_username" /></td>
		</tr>
		<tr>
			<td><?php echo __('Password') ?></td>
			<td><input type="password" value="" name="admin_password" /></td>
		</tr>
		<tr>
			<td><?php echo __('Confirm password') ?></td>
			<td><input type="password" value="" name="admin_password_confirm" /></td>
		</tr>	
	</table>
<?php endif; ?>
<?php if ($step == 3) : ?>
	<?php echo __('The installation of viennaCMS is now complete. Click Next to go to your new site.'); ?>
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
<input type="button" onclick="location.href='index.php?step=1'" value="<?php echo __('Restart') ?>" />
<input type="submit" value="<?php echo __('Next') ?> &raquo;" name="submitIt" id="submitIt"<?php echo $disabled ?> />
</th>
</table>
</form>
</body>
</html>