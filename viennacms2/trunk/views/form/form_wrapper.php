<form method="post" action="<?php echo $this['action'] ?>">
	<?php echo $this['fields'] ?>
	<div class="form-submit-div">
		<input type="submit" name="<?php echo $this['form']->form_id . '_submit' ?>" value="<?php echo __('Submit') ?>" />
	</div>
</form>