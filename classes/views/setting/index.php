<div class="wrap">

<div class="icon32" id="icon-options-general"><br></div>
<h2 style="margin-bottom: 10px;"><?php _e('Ranks Setting', 'ranks'); ?></h2>

<table class="form-table ranks-form-table">
	<tr>
		<th>
			<p><strong><?php _e('Pattern','ranks');?></strong></p>
			<p><?php _e('A setup of the conditions included in ranking','ranks');?></p>
		</th>
		<td>
<?php foreach ($patterns as $pattern_key => $pattern) : ?>
			<div class="ranks-box">
				<div class="ranks-box-label"><?php echo $pattern['label']; ?></div>
				<div class="ranks-box-columns">
					<div class="ranks-header">
						<?php echo $pattern_key; ?>
					</div>
					<dd class="ranks-ratedata">
<?php foreach($pattern['rates'] as $account_slug => $rate) : if ($rate == 0 || !isset($accounts[$account_slug]) || !$accounts[$account_slug]['status']) continue; ?>
						<div class="ranks-rates-input <?php echo $account_slug; ?>">
							<span class="ranks-rates-label"><?php echo $accounts[$account_slug]['label']; ?></span>
							× <?php echo $rate; ?>
						</div>
<?php endforeach; ?>
					</dd>
					<div class="ranks-posttypedata">
						<?php
							$types = array();
							foreach ($pattern['post_type'] as $post_type) {
								$post_type_object = get_post_type_object($post_type);
								$types[] = $post_type_object->label;// . ' <span class="description">(' . $post_type_object->name . ')</span>';
							}
							echo join('<br>', $types);
						?>
					</div>
					<dl class="ranks-datalist">
						<dt><span><?php _e('Total period','ranks');?></span></dt>
						<dd><?php
							$unit = array_shift(array_keys($pattern['term']));
							$n = $pattern['term'][$unit];
							echo sprintf(__($terms[$unit],'ranks'), $n);
              //it's dumy
              __('year','ranks');
              __('month','ranks');
              __('week','ranks');
              __('day','ranks');
              ?>
            </dd>
<?php if (!empty($pattern['schedule_event'])) : ?>
						<dt><span><?php _e('Automatic total','ranks');?></span></dt>
						<dd><?php
							switch ($pattern['schedule_event']['type']) {
								case 'daily':
									echo __('daily','ranks');
									break;
								case 'weekly':
									echo __('weekly','ranks') . $wp_locale->get_weekday($pattern['schedule_event']['week']);
									break;
								case 'monthly':
									echo __('monthly','ranks') .  $pattern['schedule_event']['day'];
									break;
							}
							echo ' ' . $pattern['schedule_event']['hour'] . __('performs','ranks');
							// echo ' <span class="description">(次回予定: ' . date_i18n(__('m/d/Y G','ranks'), $pattern['next_schedule'] + (get_option('gmt_offset') * 3600)) . ')</span>';
						?></dd>
<?php endif; ?>
<?php if (!empty($pattern['rewrite_rule'])) : ?>
						<dt><span><?php _e('Ranking page','ranks');?></span></dt>
						<dd><?php
							$url = home_url($pattern['rewrite_rule']);
							echo '<a href="' . $url . '" target="_blank">' . $url . '</a>';
						?></dd>
<?php endif; ?>
					</dl>
				</div>
				<div class="ranks-action">
					<a href="<?php echo $this->url('target_score', array('key'=>$pattern_key)); ?>" class="ranks-action-button"><?php _e('Total execution','ranks');?></a>
					<a href="<?php echo $this->url('target_preview', array('key'=>$pattern_key)); ?>" class="ranks-action-button"><?php _e('Ranking check','ranks');?></a>
					<a href="<?php echo $this->url('target_edit', array('key'=>$pattern_key)); ?>" class="ranks-action-button ranks-action-right"><?php _e('setting','ranks');?></a>
				</div>
			</div>
<?php endforeach; ?>
			<a href="<?php echo $this->url('target_new'); ?>" class="ranks-box-link"><?php _e('Add Pattern','ranks');?></a>
		</td>
	</tr>
	<tr>
		<th>
			<p><strong><?php _e('DataSource','ranks');?></strong></p>
			<p><?php _e('Setting social data','ranks');?></p>
		</th>
		<td>
<?php foreach ($accounts as $account_slug => $account) : ?>
			<div class="ranks-box <?php echo $account_slug; if (!$account['status']) echo ' invalid'; ?>">
				<div class="ranks-box-label"><?php echo $account['label']; ?></div>
				<div class="ranks-box-columns">
					<dl class="ranks-datalist">
						<dt><span><?php _e('Status','ranks');?></span></dt>
						<dd><?php echo $account['status'] ? __('enable','ranks') : __('disable','ranks'); ?></dd>
<?php if (isset($account['profile_id'])) : ?>
						<dt><span><?php _e('profile','ranks');?></span></dt>
						<dd>
							<?php echo $account['profile_id'] ? 'ga:'.$account['profile_id'] : ''; ?>
						</dd>
<?php endif; ?>
<?php if (isset($account['term'])) : ?>
						<dt><span><?php _e('acquisition period','ranks');?></span></dt>
						<dd><?php
							$unit = array_shift(array_keys($account['term']));
							$n = $account['term'][$unit];
							echo sprintf($terms[$unit], $n) . ' <span class="description">(' . date(__('n/j/Y','ranks'), strtotime("$n $unit ago")) . __(' - ','ranks') . date(__('n/j/Y','ranks')) . ')</span>';
						?></dd>
<?php endif; ?>
					</dl>
				</div>
				<div class="ranks-action">
<?php if ($account['status']) : ?>
					<a href="<?php echo $this->url('account_count', array('account'=>$account_slug)); ?>" class="ranks-action-button"><?php _e('Update data','ranks');?></a>
					<a href="<?php echo $this->url('account_preview', array('account'=>$account_slug)); ?>" class="ranks-action-button"><?php _e('View data','ranks');?></a>
<?php else : ?>
					<a href="javascript:void(0);" class="ranks-action-button ranks-action-disabled"><?php _e('Update data','ranks');?></a>
					<a href="javascript:void(0);" class="ranks-action-button ranks-action-disabled"><?php _e('View data','ranks');?></a>
<?php endif; ?>
					<a href="<?php echo $this->url("account_{$account_slug}") ?>" class="ranks-action-button ranks-action-right"><?php _e('Setting change ','ranks');?></a>
				</div>
			</div>
<?php endforeach; ?>
		</td>
	</tr>
	<tr>
		<th>
			<p><strong><?php _e('Total schedule ','ranks');?></strong></p>
			<p><?php _e('Automatic Total Planned','ranks');?></p>
			<p><?php _e('Server Time','ranks');?>:<br><?php echo date_i18n('Y-m-d H:i:s'); ?></p>
		</th>
		<td>

<table class="ranks-schedule-table">
	<thead>
		<tr>
			<th><?php _e('Time','ranks');?></th>
			<th><?php _e('Pattern','ranks');?></th>
			<th><?php _e('Use DataSource','ranks');?></th>
			<th><?php _e('Remainder','ranks');?></th>
		</tr>
	</thead>
	<tbody>
<?php if (!empty($schedule)) : foreach ($schedule as $log) : $now = time(); ?>
		<tr style="<?php echo $log['timestamp'] < $now ? 'color: red;' : ''; ?>">
			<td><?php echo date_i18n('Y-m-d H:i:s', $log['timestamp'] + ( get_option( 'gmt_offset' ) * 3600 )); ?></td>
			<td><?php echo $log['pattern_label']; ?></td>
			<td><?php echo join(', ', $log['account_label']); ?></td>
			<td><?php
				$time = $log['timestamp'] - $now;
				if ($time < 0) {
					echo __('処理中','ranks');
				} elseif ($time < 60) {
					echo number_format_i18n($time) . __('秒','ranks');
				} elseif ($time < 3600) {
					echo number_format_i18n(floor($time/60)) . __('分','ranks');
					echo number_format_i18n($time%60) . __('秒','ranks');
				} elseif ($time < 86400) {
					echo number_format_i18n(ceil($time/3600)) . __('時間','ranks');
				} else {
					echo number_format_i18n(ceil($time/86400)) . __('日','ranks');;
				}
			?></td>
		</tr>
<?php endforeach; endif; ?>
	</tbody>
</table>

		</td>
	</tr>
	<tr>
		<th>
			<p><strong><?php _e('Total history','ranks');?></strong></p>
			<p><?php _e('Performed total history','ranks');?></p>
		</th>
		<td>

<table class="ranks-schedule-table">
	<thead>
		<tr>
			<th><?php _e('Time','ranks');?></th>
			<th><?php _e('Job','ranks');?></th>
			<th><?php _e('Processing time','ranks');?></th>
			<th><?php _e('Classification','ranks');?></th>
		</tr>
	</thead>
	<tbody>
<?php if (!empty($logs)) : $l = 0; foreach ($logs as $log) : $l++; if ($l > 10) break; ?>
		<tr>
			<td><?php echo date_i18n('Y-m-d H:i:s', $log['timestamp']); ?></td>
			<td><?php echo $log['label']; ?></td>
			<td class="microtime"><?php
				if ($log['time'] < 10) {
					echo number_format_i18n($log['time'], 1) . __('秒','ranks');
				} elseif ($log['time'] < 60) {
					echo number_format_i18n($log['time']) . __('秒','ranks');
				} else {
					echo number_format_i18n(floor($log['time']/60)) . __('分','ranks');
					echo number_format_i18n($log['time']%60) . __('秒','ranks');
				}
			?></td>
			<td><?php echo $log['method'] == 'manual' ? __('手動','ranks') : __('自動','ranks'); ?></td>
		</tr>
<?php endforeach; endif; ?>
	</tbody>
</table>

		</td>
	</tr>
</table>

</div>