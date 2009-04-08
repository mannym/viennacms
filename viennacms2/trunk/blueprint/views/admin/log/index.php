<table class="extensions" style="width: 100%; margin-top: 5px;">
	<thead>
	<tr>
		<th style="width: 15%;">
			<?php echo __('Source') ?>
		</th>
		<th>
			<?php echo __('Message') ?>
		</th>
		<th>
			<?php echo __('User') ?>
		</th>
		<th>
			<?php echo __('Type') ?>
		</th>
	</tr>
	</thead>
	<?php
	foreach ($this['logs'] as $item) {
		?>
		<tr class="logitem log-<?php echo $item->log_type ?>">
			<td>
				<?php echo $item->log_source ?>
			</td>
			<td>
				<?php echo htmlspecialchars($item->log_message) ?>
			</td>
			<td>
				<?php
				$user = new VUser();
				$user->user_id = $item->log_user;
				$user->read(true); 
				echo (!empty($user->username)) ? $user->username : __('Anonymous');
				?>
			</td>
			<td>
				<?php 
				switch ($item->log_type) {
					case 'warn':
						echo __('Warning');
						break;
					case 'info':
						echo __('Information');
						break;
					case 'error':
						echo __('Error');
						break;
				}
				?>
			</td>
		</tr>
		<?php
	}
	?>
</table>