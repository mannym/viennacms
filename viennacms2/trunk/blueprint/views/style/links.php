<?php
foreach ($this['links'] as $link) {
	?>
			<li class="<?php echo $link['class'] ?>">
				<a href="<?php echo $link['link'] ?>"><?php echo $link['title'] ?></a>
			</li>
	<?php
}
