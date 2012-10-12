<div class="wrap">

<div class="icon32" id="icon-options-general"><br></div>
<h2 style="margin-bottom: 10px;"><?php echo $title; ?></h2>

<table class="form-table ranks-form-table">
	<tr>
		<th>
			<p><strong>集計パターン</strong></p>
			<p>ランキングに含める条件の設定</p>
		</th>
		<td>
<?php foreach ($patterns as $pattern_key => $pattern) : ?>
			<div class="ranks-box">
				<div class="ranks-box-label"><?php echo $pattern['label']; ?></div>
				<dl class="ranks-datalist">
					<dt>パターンキー</dt>
					<dd><?php echo $pattern_key; ?></dd>
					<dt>レート</dt>
					<dd>
<?php foreach($pattern['rates'] as $account_slug => $rate) : if ($rate == 0 || !isset($accounts[$account_slug]) || !$accounts[$account_slug]['status']) continue; ?>
						<div class="ranks-rates-input <?php echo $account_slug; ?>">
							<span class="ranks-rates-label"><?php echo $accounts[$account_slug]['label']; ?></span>
							× <?php echo $rate; ?>
						</div>
<?php endforeach; ?>
					</dd>
					<dt>投稿タイプ</dt>
					<dd><?php
						$types = array();
						foreach ($pattern['post_type'] as $post_type) {
							$post_type_object = get_post_type_object($post_type);
							$types[] = $post_type_object->label . ' <span class="description">(' . $post_type_object->name . ')</span>';
						}
						echo join('<br>', $types);
					?></dd>
					<dt>表示件数</dt>
					<dd><?php echo number_format_i18n($pattern['posts_per_page']); ?>位まで表示</dd>
					<dt>集計期間</dt>
					<dd><?php
						$unit = array_shift(array_keys($pattern['term']));
						$n = $pattern['term'][$unit];
						echo sprintf($terms[$unit], $n) . ' <span class="description">(' . date('Y年n月j日', strtotime("$n $unit ago")) . ' から ' . date('Y年n月j日') . ')</span>';
					?></dd>
					<dt>自動集計</dt>
					<dd><?php
						if (empty($pattern['schedule_event'])) {
							echo '<span class="description">(使用しない)</span>';
						} else {
							switch ($pattern['schedule_event']['type']) {
								case 'daily':
									echo '毎日 ';
									break;
								case 'weekly':
									echo '毎週 ' . $wp_locale->get_weekday($pattern['schedule_event']['week']);
									break;
								case 'monthly':
									echo '毎月 ' .  $pattern['schedule_event']['day'] . '日';
									break;
							}
							echo ' ' . $pattern['schedule_event']['hour'] . '時に実行';
							echo ' <span class="description">(次回予定: ' . date_i18n('Y年n月j日 G時', $pattern['next_schedule']) . ')</span>';
						}
					?></dd>
					<dt>ランキングページ</dt>
					<dd><?php
						if (empty($pattern['rewrite_rule'])) {
							echo '<span class="description">(使用しない)</span>';
						} else {
							$url = home_url($pattern['rewrite_rule']);
							echo '<a href="' . $url . '" target="_blank">' . $url . '</a>';
						}
					?></dd>
				</dl>
				<div class="ranks-action">
					<a href="<?php echo $this->url('target_score', array('key'=>$pattern_key)); ?>" class="ranks-action-button">集計実行</a>
					<a href="<?php echo $this->url('target_preview', array('key'=>$pattern_key)); ?>" class="ranks-action-button">ランキング確認</a>
					<a href="<?php echo $this->url('target_edit', array('key'=>$pattern_key)); ?>" class="ranks-action-button">設定変更</a>
				</div>
			</div>
<?php endforeach; ?>
			<a href="<?php echo $this->url('target_new'); ?>" class="ranks-box-link">集計パターン追加</a>
		</td>
	</tr>
	<tr>
		<th>
			<p><strong>データソース</strong></p>
			<p>使用するソーシャルデータの設定</p>
		</th>
		<td>
<?php foreach ($accounts as $account_slug => $account) : ?>
			<div class="ranks-box <?php echo $account_slug; if (!$account['status']) echo ' invalid'; ?>">
				<div class="ranks-box-label"><?php echo $account['label']; ?></div>
				<dl class="ranks-datalist">
					<dt>ステータス</dt>
					<dd><?php echo $account['status'] ? '有効' : '無効'; ?></dd>
<?php if (isset($account['profile_id'])) : ?>
					<dt>プロファイル</dt>
					<dd>
						<?php echo $account['profile_name'] ? $account['profile_name'] : '未設定'; ?>
						<span class="description">(<?php echo $account['profile_id'] ? $account['profile_id'] : ''; ?>)</span>
					</dd>
<?php endif; ?>
<?php if (isset($account['term'])) : ?>
					<dt>データ取得期間</dt>
					<dd><?php
						$unit = array_shift(array_keys($account['term']));
						$n = $account['term'][$unit];
						echo sprintf($terms[$unit], $n) . ' <span class="description">(' . date('Y年n月j日', strtotime("$n $unit ago")) . ' から ' . date('Y年n月j日') . ')</span>';
					?></dd>
<?php endif; ?>
				</dl>
				<div class="ranks-action">
<?php if ($account['status']) : ?>
					<a href="<?php echo $this->url('account_count', array('account'=>$account_slug)); ?>" class="ranks-action-button">集計実行</a>
					<a href="<?php echo $this->url('account_preview', array('account'=>$account_slug)); ?>" class="ranks-action-button">ランキング確認</a>
<?php endif; ?>
					<a href="<?php echo $this->url("account_{$account_slug}") ?>" class="ranks-action-button">設定変更</a>
				</div>
			</div>
<?php endforeach; ?>
		</td>
	</tr>
</table>

</div>