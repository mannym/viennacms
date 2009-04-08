<?php
$i = 0;

foreach ($this['values'] as $key => $value) {
	if (is_numeric($key)) {
		$key = $value;
	}
	
	$myid = $this['name'] . '-' . $i;
	
	?>
	<input class="radiobox <?php echo $this['class'] ?>" type="radio" value="<?php echo $value ?>"<?php echo ($value == $this['value']) ? ' checked="checked"' : ''; ?> name="<?php echo $this['name'] ?>" id="<?php echo $myid ?>" />
	<label class="radiolabel <?php echo $this['class'] ?>" for="<?php echo $myid ?>"><?php echo $key ?></label>
	<?php
	
	$i++;
}
?>