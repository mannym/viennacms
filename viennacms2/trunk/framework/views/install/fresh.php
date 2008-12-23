<form method="post" action="<?php echo $this['action'] ?>">
<?php
if ($this['step'] == 1):
?>
<?php echo __('Welcome to the viennaCMS2 installation wizard! viennaCMS2 is a rewritten version of the original viennaCMS, and includes a lot of new features.<br />Please note this is a development version, which will mean that critical bugs will occur. For your own safety, and the safety of others, please refrain from using viennaCMS2 on a live site.') ?>
<?php	
endif;
?>
<input type="hidden" name="step" value="<?php echo $this['step'] + 1 ?>" />
<div style="text-align: center;">
<input type="submit" value="<?php echo __('Next &raquo;') ?>" />
</div>
</form>