<?php
foreach ($this['links'] as $link) {
	?>
			<li>
				<a href="<?php echo $link['link'] ?>" class="<?php echo $link['class'] ?>"><?php echo $link['title'] ?></a>
			</li>
	<?php
}
