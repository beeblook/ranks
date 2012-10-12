<div id="ranks-setting" class="wrap">

<div class="icon32" id="icon-options-general"><br></div>
<h2 style="margin-bottom: 20px;"><?php echo $title; ?></h2>

<p>
	<a href="<?php echo $this->url('index'); ?>">←戻る</a>
</p>

<h3>集計パターン 新規設定登録</h3>

<form action="" method="post">

	<?php if ($message) echo $message; ?>

	<table class="form-table ranks-form-table">
		<tr>
			<th>
				<strong>ランキング</strong><br>
				任意の名称を設定してください。
			</th>
			<td>
				<input class="regular-text" type="text" name="label" placeholder="名称" value="<?php echo esc_attr($pattern['label']); ?>" /><br>
				<input class="regular-text" type="text" name="key" placeholder="キー" value="<?php echo $key; ?>" />
				<span class="description">キーは半角英数字で入力してください。</span><br>
				<span class="description">ランキングは <code>query_posts('<?php echo "{$ranks->query_var}=[キー]"; ?>');</code> で取得可能になります。</span>
			</td>
		</tr>
		<tr>
			<th>
				<strong>投稿タイプ</strong><br>
				ランキングに含める投稿タイプを選択してください。
			</th>
			<td>
<?php foreach (get_post_types(array('public'=>true)) as $post_type) : $post_type_object = get_post_type_object($post_type); ?>
				<label><input type="checkbox" name="post_type[]" value="<?php echo esc_attr($post_type); ?>" <?php checked(in_array($post_type, $pattern['post_type'])); ?> /> <?php echo $post_type_object->label; ?></label><br>
<?php endforeach; ?>
			</td>
		</tr>
		<tr>
			<th>
				<strong>集計期間</strong><br>
				集計する期間を設定してください。
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
				<strong>レート</strong><br>
				各データソースのレートを設定してください。
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
	</table>
	<p class="submit">
		<input class="button-primary" type="submit" name="submit" value="設定を登録" />
	</p>

</form>

</div>