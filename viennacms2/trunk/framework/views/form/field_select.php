<select class="selectbox <?php echo $this['class'] ?>" name="<?php echo $this['name'] ?>" id="<?php echo $this['name'] ?>">
	<?php
	foreach ($this['values'] as $key => $value) {
		if (is_numeric($key)) {
			$key = $value;
		}
		
		?>
		<option value="<?php echo $value ?>"<?php echo ($value == $this['value']) ? ' selected="selected"' : ''; ?>><?php echo $key ?></option>
		<?php
	}
	?>
</select>