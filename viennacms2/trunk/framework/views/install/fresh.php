<form method="post" action="<?php echo $this['action'] ?>">
<?php
if ($this['step'] == 1):
?>
<?php echo __('Welcome to the viennaCMS2 installation wizard! viennaCMS2 is a rewritten version of the original viennaCMS, and includes a lot of new features.<br />Please note this is a development version, which will mean that critical bugs will occur. For your own safety, and the safety of others, please refrain from using viennaCMS2 on a live site.') ?>
<?php
elseif ($this['step'] == 2):
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
<span class="form-description"><?php echo __('The name of the database to install viennaCMS in.') ?></span>
<label for="table_prefix" class="form-title"><?php echo __('Table prefix') ?>:</label>
<input type="text" name="table_prefix" id="table_prefix" value="<?php echo $this['table_prefix'] ?>" class="textbox" />
<span class="form-description"><?php echo __('The prefix of the viennaCMS tables. If you don\'t have other viennaCMS installations in the same database, you can keep this at the default setting.') ?></span>

<?php	
elseif ($this['step'] == 3):
if ($this['error'] != '') {
	echo '<span style="color: red;">' . $this['error'] . '</span>';
}
?>
<label for="username" class="form-title"><?php echo __('User name') ?>:</label>
<input type="text" name="username" id="username" value="<?php echo $this['username'] ?>" class="textbox" />
<span class="form-description"><?php echo __('The user name you want to use to log in to the administration panel.') ?></span>
<label for="password" class="form-title"><?php echo __('Password') ?>:</label>
<input type="password" name="password" id="password" class="textbox" />
<span class="form-description"><?php echo __('The password you want to use. Don\'t forget this.') ?></span>
<label for="password2" class="form-title"><?php echo __('Password (confirm)') ?>:</label>
<input type="password" name="password2" id="password2" class="textbox" />
<span class="form-description"><?php echo __('Type the password again, so we can check for typing mistakes.') ?></span>
<?php
endif;
?>
<input type="hidden" name="step" value="<?php echo $this['step'] + 1 ?>" />
<div style="text-align: center;">
<input type="submit" value="<?php echo __('Next &raquo;') ?>" />
</div>
</form>