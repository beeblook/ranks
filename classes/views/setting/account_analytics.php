<div id="ranks-setting" class="wrap">

<div class="icon32" id="icon-options-general"><br></div>
<h2 style="margin-bottom: 20px;"><?php echo $title; ?></h2>

<p>
	<a href="<?php echo $this->url('index'); ?>">←戻る</a>
</p>

<h3><?php echo $accounts['analytics']['label']; ?> 設定変更</h3>

<form action="" method="post">

	<?php if ($message) echo $message; ?>

<?php if (isset($accounts['analytics']['profile_id'])) : ?>

	<table class="form-table ranks-form-table">
		<tr>
			<th><strong>ステータス</strong></th>
			<td><label><input type="checkbox" name="enable" <?php checked($accounts['analytics']['status']); ?> /> 有効</label></td>
		</tr>
		<tr>
			<th><strong>プロファイル</strong></th>
			<td><span class="ranks-saved-value"><?php echo $accounts['analytics']['profile_name']; ?></span></td>
		</tr>
		<tr>
			<th><strong>プロファイルID</strong></th>
			<td><span class="ranks-saved-value"><?php echo $accounts['analytics']['profile_id']; ?></span></td>
		</tr>
		<tr>
			<th><strong>データ取得期間</strong></th>
			<td>
				<input type="text" name="term[n]" value="<?php echo esc_attr(array_shift(array_values($accounts['analytics']['term']))); ?>" size="2" />
				<select name="term[unit]">
<?php foreach ($terms as $term => $term_format) : ?>
					<option value="<?php echo esc_attr($term); ?>" <?php selected(isset($accounts['analytics']['term'][$term])); ?> /> <?php echo sprintf($term_format, ''); ?></option>
<?php endforeach; ?>
				</select>
			</td>
		</tr>
	</table>
	<p class="submit">
		<input class="button-primary" type="submit" name="submit" value="保存" />
		<input class="ranks-remove-button" type="submit" name="clear" value="設定を削除" />
	</p>

<?php elseif (empty($account_data)) : ?>

	<table class="form-table ranks-form-table">
		<tr>
			<th><strong>メールアドレス</strong></th>
			<td><input class="regular-text" type="email" name="mailaddress" /></td>
		</tr>
		<tr>
			<th><strong>パスワード</strong></th>
			<td><input class="regular-text" type="password" name="password" /></td>
		</tr>
	</table>
	<p class="submit">
		<input class="button-primary" type="submit" name="submit" value="ログイン" />
	</p>

<?php else : ?>

	<table class="form-table ranks-form-table">
		<tr>
			<th><strong>プロファイル</strong></th>
			<td>
				<select id="profile_id" name="profile_id">
<?php foreach ($account_data as $account) : ?>
					<option value="<?php echo $account->getProfileId(); ?>"><?php echo $account->getProfileName(); ?></option>
<?php endforeach; ?>
				</select>
			</td>
		</tr>
	</table>
	<p class="submit">
		<input class="button-primary" type="submit" name="submit" value="プロファイルを保存" />
		<input class="ranks-remove-button" type="submit" name="clear" value="設定を削除" />
	</p>

<?php endif; ?>

</form>

</div>