<div id="ranks-setting" class="wrap">

<div class="icon32" id="icon-options-general"><br></div>
<h2 style="margin-bottom: 20px;"><?php echo $title; ?></h2>

<p>
	<a href="<?php echo $this->url('index'); ?>">←<?php _e('back');?></a>
</p>

<h3><?php echo $accounts['analytics']['label']; ?> 設定変更</h3>

<form action="" method="post">

	<?php if ($message) echo $message; ?>

<?php if ($profile) : ?>

	<table class="form-table ranks-form-table">
		<tr>
			<th><strong>ステータス</strong></th>
			<td><label><input type="checkbox" name="enable" <?php checked($accounts['analytics']['status']); ?> /> 有効</label></td>
		</tr>
		<tr>
			<th><strong>プロパティID</strong></th>
			<td><span class="ranks-saved-value"><?php echo $profile['property_id']; ?></span></td>
		</tr>
		<tr>
			<th><strong>プロファイル</strong></th>
			<td><span class="ranks-saved-value"><?php echo $profile['profile_name']; ?></span></td>
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
<?php elseif ( !isset($accounts['analytics']['app_id'] ) || !isset($accounts['analytics']['app_secret'] ) ): ?>

	<table class="form-table ranks-form-table">
		<tr>
			<th><strong>Client ID</strong></th>
			<td><input type="text" name="app_id" value="<?php echo $accounts['analytics']['api_id'] ?>" /></td>
		</tr>
		<tr>
			<th><strong>Client Secret</strong></th>
			<td><input type="text" name="app_secret" value="<?php echo $accounts['analytics']['api_secret'] ?>" /></td>
		</tr>
	</table>
	<p class="submit">
		<input class="button-primary" type="submit" name="submit" value="保存" />
	</p>

<?php elseif (empty($selection)) : ?>

	<table class="form-table ranks-form-table">
		<tr>
			<th><strong>認証コード</strong></th>
			<td>
				<input type="text" name="code" size="70" />
				<a class="button" href="javascript:void(0);" onclick="window.open('<?php echo $google_auth_url; ?>', 'activate','width=700, height=600, menubar=0, status=0, location=0, toolbar=0');">認証コードを取得する</a>
			</td>
		</tr>
	</table>
	<p class="submit">
		<input class="button-primary" type="submit" name="submit" value="認証コードを送信" />
		<input class="ranks-remove-button" type="submit" name="clear" value="設定を削除" />
	</p>

<?php else : ?>

	<table class="form-table ranks-form-table">
		<tr>
			<th><strong>プロファイル</strong></th>
			<td>
<?php foreach ($selection as $profile_id => $profile) : ?>
				<div>
					<label>
						<input type="radio" name="profile_id" value="<?php echo $profile_id; ?>">
						<span class="gray"><?php echo $profile['property_id']; ?></span>
						<strong><?php echo $profile['profile_name']; ?></strong>
						- <?php echo $profile['website_url']; ?>
					</label>
				</div>
<?php endforeach; ?>
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