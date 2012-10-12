<div id="ranks-setting" class="wrap">

<div class="icon32" id="icon-options-general"><br></div>
<h2 style="margin-bottom: 20px;"><?php echo $title; ?></h2>

<p>
	<a href="<?php echo $this->url('index'); ?>">←戻る</a>
</p>

<h3>集計パターン 設定変更</h3>

<form action="" method="post">

	<?php if ($message) echo $message; ?>

	<div class="ranks-form-block">
		<input class="ranks-title-input" type="text" name="label" placeholder="名称" value="<?php echo esc_attr($pattern['label']); ?>" /><br>
		<span class="description">ランキングは <code>query_posts('<?php echo "{$ranks->query_var}={$key}"; ?>');</code> または <code>get_posts('<?php echo "{$ranks->query_var}={$key}"; ?>');</code> で取得可能です。</span>
	</div>

	<table class="form-table ranks-form-table">
		<tr>
			<th>
				<strong>ランキングキー</strong><br>
				任意の名称
			</th>
			<td>
				<input class="regular-text" type="text" name="key" placeholder="キー" value="<?php echo $key; ?>" readonly="readonly" /><br>
				<span class="description">キーは変更できません。</span>
			</td>
		</tr>
		<tr>
			<th>
				<strong>レート</strong><br>
				各データソースのレートの設定
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
				<strong>投稿タイプ</strong><br>
				ランキングに含める投稿タイプ
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
				<strong>表示件数</strong><br>
				表示する投稿の件数
			</th>
			<td>
				<label><input type="text" name="posts_per_page" value="<?php echo esc_attr($pattern['posts_per_page']); ?>" size="2" />
				位まで表示</label><br>
				<span class="description">同位の投稿が複数ある場合、最後の順位まで表示されない場合があります。</span>
			</td>
		</tr>
		<tr>
			<th>
				<strong>集計期間</strong><br>
				ランキングに含める期間
			</th>
			<td>
				<input type="text" name="term[n]" value="<?php echo esc_attr(array_shift(array_values($pattern['term']))); ?>" size="2" />
				<select name="term[unit]">
<?php foreach ($terms as $term => $term_format) : ?>
					<option value="<?php echo esc_attr($term); ?>" <?php selected(isset($pattern['term'][$term])); ?> /> <?php echo sprintf($term_format, ''); ?></option>
<?php endforeach; ?>
				</select><br>
				<span class="description">本日から設定した期間の投稿が対象になります。</span>
			</td>
		</tr>
		<tr>
			<th>
				<strong>自動集計</strong><br>
				自動集計の間隔
			</th>
			<td>
				<label><input data-toggle="ranks-schedule-event" type="checkbox" name="enable_schedule_event" value="enable" <?php checked(!empty($pattern['schedule_event'])); ?> /> 自動集計を有効にする</label><br>
				<div id="ranks-schedule-event" class="ranks-toggle">
					<div class="ranks-schedule-type">
						<label><input type="radio" name="schedule_event[type]" value="daily" <?php checked('daily', $pattern['schedule_event']['type']); ?> /> 毎日</label>
					</div>
					<div class="ranks-schedule-type">
						<label><input data-toggle="ranks-schedule-weekly" type="radio" name="schedule_event[type]" value="weekly" <?php checked('weekly', $pattern['schedule_event']['type']); ?> /> 毎週</label>
						<span id="ranks-schedule-weekly" class="ranks-toggle">
							<select name="schedule_event[week]">
<?php for ($i = 0; $i <= 6; $i++) : ?>
								<option value="<?php echo esc_attr($i); ?>" <?php selected($i, $pattern['schedule_event']['week']); ?>><?php echo $wp_locale->get_weekday($i); ?></option>
<?php endfor; ?>
							</select>
						</span>
					</div>
					<div class="ranks-schedule-type">
						<label><input data-toggle="ranks-schedule-monthly" type="radio" name="schedule_event[type]" value="monthly" <?php checked('monthly', $pattern['schedule_event']['type']); ?> /> 毎月</label>
						<span id="ranks-schedule-monthly" class="ranks-toggle">
							<select name="schedule_event[day]">
<?php for ($i = 1; $i <= 31; $i++) : ?>
								<option value="<?php echo esc_attr($i); ?>" <?php selected($i, $pattern['schedule_event']['day']); ?>><?php echo esc_html($i) ?></option>
<?php endfor; ?>
							</select>
							日
						</span>
					</div>
					<select name="schedule_event[hour]">
<?php for ($i = 0; $i <= 23; $i++) : ?>
						<option value="<?php echo esc_attr($i); ?>" <?php selected($i, $pattern['schedule_event']['hour']); ?>><?php echo esc_html($i) ?></option>
<?php endfor; ?>
					</select>
					時に実行<br>
					<span class="description">WordPress CRON API により定期実行します。</span>
				</div>
			</td>
		</tr>
		<tr>
			<th>
				<strong>ランキングページ</strong><br>
				専用ページの生成
			</th>
			<td>
				<label><input data-toggle="ranks-rewrite-rule" type="checkbox" name="create_rewrite_rule" value="create" <?php checked(!empty($pattern['rewrite_rule'])); ?> /> ランキングページを生成する</label><br>
				<div id="ranks-rewrite-rule" class="ranks-toggle">
					<label class="ranks-rewrite-rule-path"><?php echo home_url('/'); ?><input class="regular-text" type="text" name="rewrite_rule" value="<?php echo esc_attr($pattern['rewrite_rule']); ?>" size="10" /></label><br>
					<span class="description">ランキングページは固定ページより優先して表示されます。</span>
				</div>
			</td>
		</tr>
	</table>

	<p class="submit">
		<input class="button-primary" type="submit" name="submit" value="設定を変更" />
		<input class="ranks-remove-button" type="submit" name="clear" value="設定を削除" />
	</p>

</form>


<h4>集計履歴</h4>

<table class="ranks-posts-table">
	<thead>
		<tr>
			<th>種別</th>
			<th>日時</th>
			<th>処理時間</th>
			<th>処理結果</th>
		</tr>
	</thead>
	<tbody>
<?php if ($pattern['next_schedule']) : ?>
		<tr>
			<td>予定</td>
			<td><?php echo date_i18n('Y-m-d H:i:s', $pattern['next_schedule']); ?></td>
			<td>-</td>
			<td>-</td>
		</tr>
<?php endif; ?>
<?php if (!empty($pattern['log'])) : foreach ($pattern['log'] as $i => $log) : ?>
		<tr>
			<td><?php echo $log['method'] == 'manual' ? '手動' : '自動'; ?></td>
			<td><?php echo date_i18n('Y-m-d H:i:s', $log['timestamp']); ?></td>
			<td><?php echo number_format_i18n($log['processing_time'], 3) . 'ms'; ?></td>
			<td><a href="javascript:void(0);<?php //echo $this->url('target_preview', array('key'=>$key, 'log'=>$i)); ?>">確認</a></td>
		</tr>
<?php endforeach; endif; ?>
	</tbody>
</table>

</div>