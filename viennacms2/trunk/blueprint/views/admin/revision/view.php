<?php
echo '<h1>' . sprintf(__('%s, revision %d'), $this['node']->title, $this['node']->revision->number) . '</h1>';
echo '<p>' . sprintf(__('Created on %s'), $this['revision_date']) . '</p>';
echo '<div class="preview">';
echo $this['revision_content'];
echo '</div>';