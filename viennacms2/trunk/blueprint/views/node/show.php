<?php
if ($this['type'] == 'static') {
	echo $this['node']->revision->content;
} else {
	echo $this['content'];
}