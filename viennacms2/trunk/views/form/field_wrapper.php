<label for="<?php echo $this['name'] ?>" class="form-title"><?php echo $this['label'] ?>: <?php echo ($this['required']) ? '<span class="form-required">*</span>' : '' ?></label>
<?php echo $this['rendered_field']; ?>
<?php if ($this['error']) { ?>
	<span class="form-error"><?php echo $this['error'] ?></span>
<?php } ?>
<span class="form-description"><?php echo $this['description'] ?></span>
