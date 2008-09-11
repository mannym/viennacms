<form action="" method="post" style="text-align: center;">
	<table>
		<tr>
			<td><label for="username"><?php echo __('Username') ?>: </label></td>
			<td><input type="text" name="username" id="username" /></td>
		</tr>
		<tr>
			<td><label for="password"><?php echo __('Password') ?>: </label></td>
			<td><input type="password" name="password" id="password" /></td>
		</tr>
		<tr>
			<td colspan="2">
				<input type="submit" name="submit" value="<?php echo __('Login') ?>" />
			</td>
		</tr>
	</table>
</form>