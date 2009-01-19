<form method="post" action="<?php echo $this['action'] ?>">
<?php
if ($this['step'] == 1):
?>
<?php echo __('Welcome to the viennaCMS2 conversion wizard. Enter the data from your viennaCMS1 installation here. This WILL damage one of the installations. Use with care.') ?>
<?php
if ($this['error'] != '') {
	echo '<span style="color: red;">' . $this['error'] . '</span>';
}
?>
<label for="dbhost" class="form-title"><?php echo __('Database host') ?>:</label>
<input type="text" name="dbhost" id="dbhost" value="<?php echo $this['dbhost'] ?>" class="textbox" />
<span class="form-description"><?php echo __('The database host, usually localhost. If you don\'t know this, contact your hosting provider.') ?></span>
<label for="dbuser" class="form-title"><?php echo __('Database user name') ?>:</label>
<input type="text" name="dbuser" id="dbuser" value="<?php echo $this['dbuser'] ?>" class="textbox" />
<span class="form-description"><?php echo __('The database user name to access your database. You can usually find this in your host\'s control panel.') ?></span>
<label for="dbpasswd" class="form-title"><?php echo __('Database password') ?>:</label>
<input type="password" name="dbpasswd" id="dbpasswd" class="textbox" />
<span class="form-description"><?php echo __('The password which belongs to the database user name.') ?></span>
<label for="dbname" class="form-title"><?php echo __('Database name') ?>:</label>
<input type="text" name="dbname" id="dbname" value="<?php echo $this['dbname'] ?>" class="textbox" />
<span class="form-description"><?php echo __('The name of the database to convert viennaCMS from.') ?></span>
<label for="table_prefix" class="form-title"><?php echo __('Table prefix') ?>:</label>
<input type="text" name="table_prefix" id="table_prefix" value="<?php echo $this['table_prefix'] ?>" class="textbox" />
<span class="form-description"><?php echo __('The prefix of the viennaCMS tables.') ?></span>

<?php	
else:
?>
SUCCESS!
<?php
endif;
?>
<input type="hidden" name="step" value="<?php echo $this['step'] + 1 ?>" />
<div style="text-align: center;">
<input type="submit" value="<?php echo __('Next &raquo;') ?>" />
</div>
</form>