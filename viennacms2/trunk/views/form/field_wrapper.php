<label for="<?php echo $this['name'] ?>" class="form-title"><?php echo $this['label'] ?>:</label> <?php echo ($this['required']) ? '<span class="form-required">*</span>' : '' ?><br />
<?php echo $this['rendered_field'] ?><br />
<span class="form-description"><?php echo $this['description'] ?></span><br /><br />
