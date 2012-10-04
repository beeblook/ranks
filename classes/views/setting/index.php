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
				<div class="ranks-box-label"><?php echo $pattern['label']; ?> (<?php echo $pattern_key; ?>)</div>
				<dl class="ranks-datalist">
					<dt>投稿タイプ</dt>
					<dd><?php
						$types = array();
						foreach ($pattern['post_type'] as $post_type) {
							$post_type_object = get_post_type_object($post_type);
							$types[] = $post_type_object->label . ' <span>' . $post_type_object->name . '</span>';
						}
						echo join('<br>', $types);
					?></dd>
					<dt>集計期間</dt>
					<dd><?php
						$unit = array_shift(array_keys($pattern['term']));
						$n = $pattern['term'][$unit];
						echo sprintf($terms[$unit], $n) . ' <span>' . date('Y/n/j', strtotime("- $n $unit")) . ' - ' . date('Y/n/j') . '</span>';
					?></dd>
					<dt>レート</dt>
					<dd><?php
						foreach($pattern['rates'] as $account_slug => $rate) {
							if ($rate == 0 || !isset($accounts[$account_slug]) || !$accounts[$account_slug]['status']) continue;
?>
								<div class="ranks-rates-input <?php echo $account_slug; ?>">
									<span class="ranks-rates-label"><?php echo $accounts[$account_slug]['label']; ?></span>
									× <?php echo $rate; ?>
								</div>
<?php
						}
					?>
					</dd>
				</dl>
				<div class="ranks-action">
					<a href="<?php echo $this->url('target_score', array('key'=>$pattern_key)); ?>" class="ranks-action-button">集計実行</a>
					<a href="<?php echo $this->url('target_preview', array('key'=>$pattern_key)); ?>" class="ranks-action-button">プレビュー</a>
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
						<span><?php echo $account['profile_id'] ? $account['profile_id'] : ''; ?></span>
					</dd>
<?php endif; ?>
				</dl>
				<div class="ranks-action">
<?php if ($account['status']) : ?>
					<a href="<?php echo $this->url('account_count', array('account'=>$account_slug)); ?>" class="ranks-action-button">集計実行</a>
					<a href="<?php echo $this->url('account_preview', array('account'=>$account_slug)); ?>" class="ranks-action-button">プレビュー</a>
<?php endif; ?>
					<a href="<?php echo $this->url("account_{$account_slug}") ?>" class="ranks-action-button">設定変更</a>
				</div>
			</div>
<?php endforeach; ?>
		</td>
	</tr>
</table>

</div>