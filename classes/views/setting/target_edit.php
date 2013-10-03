<div id="ranks-setting" class="wrap">

<div class="icon32" id="icon-options-general"><br></div>
<h2 style="margin-bottom: 20px;"><?php echo $title; ?></h2>

<p>
	<a href="<?php echo $this->url('index'); ?>">←戻る</a>
</p>

<h3><?php _e('Pattern','ranks');?> <?php _e('Setting','ranks');?></h3>

<form action="" method="post">

	<?php if ($message) echo $message; ?>

  <table class="form-table ranks-form-table">
		<tr>
			<th>
				<strong><?php _e('Ranking Key','ranks');?></strong><br>
				<?php _e('Please set up arbitrary names.','ranks');?>
			</th>
			<td>
				<input class="regular-text" type="text" name="label" placeholder="<?php _e('Name','ranks');?>" value="<?php echo esc_attr($pattern['label']); ?>" /><br>
				<input class="regular-text" type="text" name="key" placeholder="<?php _e('Key','ranks');?>" value="<?php echo $key; ?>" readonly="readonly" />
				<span class="description"><?php _e('Please input the key by a half-width alphanumeric character.','ranks');?></span><br>
				<span class="description"><?php _e('Ranking is','ranks');?> <code>query_posts('<?php echo "{$ranks->query_var}=[".__('Key','ranks')."]"; ?>');</code><?php _e('becomes acquirable.','ranks');?></span>
			</td>
		</tr>
		<tr>
			<th>
				<strong><?php _e('PostType','ranks');?></strong><br>
				<?php _e('Please choose the PostType included in ranking.','ranks');?>
			</th>
			<td>
<?php foreach (get_post_types(array('public'=>true)) as $post_type) : $post_type_object = get_post_type_object($post_type); ?>
				<label><input type="checkbox" name="post_type[]" value="<?php echo esc_attr($post_type); ?>" <?php checked(in_array($post_type, $pattern['post_type'])); ?> />
				<?php echo $post_type_object->label; ?>
				<span class="description">(<?php echo $post_type_object->name; ?>)</span>
				</label><br>
<?php endforeach; ?>
			</td>
		</tr>
		<tr>
			<th>
				<strong><?php _e('Total period','ranks');?></strong><br>
				<?php _e('Please set up the period which totals.','ranks');?>
			</th>
			<td>
				<input type="text" name="term[n]" value="<?php echo esc_attr(array_shift(array_values($pattern['term']))); ?>" size="2" />
				<select name="term[unit]">
<?php foreach ($terms as $term => $term_format) : ?>
					<option value="<?php echo esc_attr($term); ?>" <?php selected(isset($pattern['term'][$term])); ?> /> <?php echo sprintf($term_format, ''); ?></option>
<?php endforeach; ?>
				</select><br>
				<span class="description"><?php _e('The contribution of a period set up from today is applicable.','ranks');?></span>
			</td>
		</tr>
		<tr>
			<th>
				<strong><?php _e('Rate','ranks');?></strong><br>
				<?php _e('Please set up the rate of each datasource.','ranks');?>
			</th>
			<td>
<?php foreach ($accounts as $account_slug => $account) : if (!$account['status']) continue; ?>
				<div class="ranks-rates-input <?php echo $account_slug; ?>">
					<span class="ranks-rates-label"><?php echo $account['label']; ?></span>
					× <input type="text" name="<?php echo "rates[{$account_slug}]"; ?>" value="<?php echo esc_attr($pattern['rates'][$account_slug]); ?>" size="2" />
				</div>
<?php endforeach; ?>
			</td>
		</tr>
		<tr>
			<th>
				<strong><?php _e('Automatic total','ranks');?></strong><br>
				<?php _e('The interval of an automatic total','ranks');?>
			</th>
			<td>
				<label><input data-toggle="ranks-schedule-event" type="checkbox" name="enable_schedule_event" value="enable" <?php checked(!empty($pattern['schedule_event'])); ?> /> <?php _e('An automatic total is validated.','ranks');?></label><br>
				<div id="ranks-schedule-event" class="ranks-toggle">
					<div class="ranks-schedule-type">
						<label><input type="radio" name="schedule_event[type]" value="daily" <?php checked('daily', $pattern['schedule_event']['type']); ?> /> <?php _e('daily','ranks');?></label>
					</div>
					<div class="ranks-schedule-type">
						<label><input data-toggle="ranks-schedule-weekly" type="radio" name="schedule_event[type]" value="weekly" <?php checked('weekly', $pattern['schedule_event']['type']); ?> /> <?php _e('weekly','ranks');?></label>
						<span id="ranks-schedule-weekly" class="ranks-toggle">
							<select name="schedule_event[week]">
<?php for ($i = 0; $i <= 6; $i++) : ?>
								<option value="<?php echo esc_attr($i); ?>" <?php selected($i, $pattern['schedule_event']['week']); ?>><?php echo $wp_locale->get_weekday($i); ?></option>
<?php endfor; ?>
							</select>
						</span>
					</div>
					<div class="ranks-schedule-type">
						<label><input data-toggle="ranks-schedule-monthly" type="radio" name="schedule_event[type]" value="monthly" <?php checked('monthly', $pattern['schedule_event']['type']); ?> /> <?php _e('monthly','ranks');?></label>
						<span id="ranks-schedule-monthly" class="ranks-toggle">
							<select name="schedule_event[day]">
<?php for ($i = 1; $i <= 31; $i++) : ?>
								<option value="<?php echo esc_attr($i); ?>" <?php selected($i, $pattern['schedule_event']['day']); ?>><?php echo esc_html($i) ?></option>
<?php endfor; ?>
							</select>
						</span>
					</div>
					<select name="schedule_event[hour]">
<?php for ($i = 0; $i <= 23; $i++) : ?>
						<option value="<?php echo esc_attr($i); ?>" <?php selected($i, $pattern['schedule_event']['hour']); ?>><?php echo esc_html($i) ?></option>
<?php endfor; ?>
					</select>
					<?php _e('Performs','ranks');?><br>
					<span class="description"><?php _e('Fixed execution is carried out by WordPress CRON API.','ranks');?></span>
				</div>
			</td>
		</tr>
		<tr>
			<th>
				<strong><?php _e('Ranking page','ranks');?></strong><br>
				<?php _e('Ranking Page generation','ranks');?>
			</th>
			<td>
				<label><input data-toggle="ranks-rewrite-rule" type="checkbox" name="create_rewrite_rule" value="create" <?php checked(!empty($pattern['rewrite_rule'])); ?> /> <?php _e('Generate page','ranks');?></label><br>
				<div id="ranks-rewrite-rule" class="ranks-toggle">
					<label class="ranks-rewrite-rule-path"><?php echo home_url('/'); ?><input class="regular-text" type="text" name="rewrite_rule" value="<?php echo esc_attr($pattern['rewrite_rule']); ?>" size="10" /></label><br>
					<span class="description"><?php _e('Priority is given to a ranking page over a fixed page, and it is displayed.','ranks');?></span><br>
					<span class="description"><?php _e('As for a template file, <code>ranks-(key).php</code><code>ranks.php</code><code>archive.php</code><code>index.php</code> is used.','ranks');?></span>
				</div>
			</td>
		</tr>
	</table>

	<p class="submit">
		<input class="button-primary" type="submit" name="submit" value="<?php _e('Save','ranks');?>" />
		<input class="ranks-remove-button" type="submit" name="clear" value="<?php _e('Remove Settings','ranks');?>" />
	</p>

</form>


<h4><?php _e('Total history','ranks');?></h4>

<table class="ranks-posts-table">
	<thead>
		<tr>
			<th><?php _e('Classification','ranks');?></th>
			<th><?php _e('Time','ranks');?></th>
			<th><?php _e('Processing time','ranks');?></th>
			<!--th><?php _e('Processing result','ranks');?></th-->
		</tr>
	</thead>
	<tbody>
<?php if ($pattern['next_schedule']) : ?>
		<tr>
			<td>予定</td>
			<td><?php echo date_i18n('Y-m-d H:i:s', $pattern['next_schedule']); ?></td>
			<td>-</td>
			<!--td>-</td-->
		</tr>
<?php endif; ?>
<?php if (!empty($pattern['log'])) : foreach ($pattern['log'] as $i => $log) : ?>
		<tr>
			<td><?php echo $log['method'] == 'manual' ? __('Manual','ranks') : __('Automatic','ranks'); ?></td>
			<td><?php echo date_i18n('Y-m-d H:i:s', $log['timestamp']); ?></td>
			<td><?php echo number_format_i18n($log['processing_time'], 3) . 'ms'; ?></td>
			<!--td><a href="<?php echo $this->url('target_preview', array('key'=>$key, 'log'=>$i)); ?>"><?php _e('View','ranks');?></a></td-->
		</tr>
<?php endforeach; endif; ?>
	</tbody>
</table>

</div>