<div id="ranks-setting" class="wrap">

<div class="icon32" id="icon-options-general"><br></div>
<h2 style="margin-bottom: 20px;"><?php echo $title; ?></h2>

<p>
	<a href="<?php echo $this->url('index'); ?>">←<?php _e('back','ranks');?></a>
</p>

<h3><?php echo $accounts['analytics']['label']; ?> <?php _e('Setting','ranks');?></h3>

<form action="" method="post">

	<?php if ($message) echo $message; ?>

<?php if ($profile) : ?>

	<table class="form-table ranks-form-table">
		<tr>
			<th><strong><?php _e('Status','ranks');?></strong></th>
			<td><label><input type="checkbox" name="enable" <?php checked($accounts['analytics']['status']); ?> /> <?php _e('Enable','ranks');?></label></td>
		</tr>
		<tr>
			<th><strong><?php _e('PropertyID','ranks');?></strong></th>
			<td><span class="ranks-saved-value"><?php echo $profile['property_id']; ?></span></td>
		</tr>
		<tr>
			<th><strong><?php _e('Profile','ranks');?></strong></th>
			<td><span class="ranks-saved-value"><?php echo $profile['profile_name']; ?></span></td>
		</tr>
		<tr>
			<th><strong><?php _e('ProfileID','ranks');?></strong></th>
			<td><span class="ranks-saved-value"><?php echo $accounts['analytics']['profile_id']; ?></span></td>
		</tr>
		<tr>
			<th><strong><?php _e('Acquisition Period','ranks');?></strong></th>
			<td>
				<input type="text" name="term[n]" value="<?php echo esc_attr(array_shift(array_values($accounts['analytics']['term']))); ?>" size="2" />
				<select name="term[unit]">
<?php foreach ($terms as $term => $term_format) : ?>
					<option value="<?php echo esc_attr($term); ?>" <?php selected(isset($accounts['analytics']['term'][$term])); ?> /> <?php echo sprintf(__($term_format,'ranks'),''); ?></option>
<?php endforeach; ?>
				</select>
			</td>
		</tr>
	</table>
	<p class="submit">
		<input class="button-primary" type="submit" name="submit" value="<?php _e('Save','ranks');?>" />
		<input class="ranks-remove-button" type="submit" name="clear" value="<?php _e('Remove Settings','ranks');?>" />
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
		<input class="button-primary" type="submit" name="submit" value="<?php _e('Save','ranks');?>" />
	</p>

  <p>
    <h2><?php _e('1.Go to <a href="https://cloud.google.com/console" target="_blank">Google Cloud Console</a>.','ranks');?></h2>
    <img src="<?php echo RANKS_URL;?>images/setting-analytics-1.png" >
  </p>

  <p>
    <h2><?php _e('2. Create a new project.','ranks');?></h2>
    <img src="<?php echo RANKS_URL;?>images/setting-analytics-2.png" >
  </p>

  <p>
    <h2><?php _e('3. Enabling Analytics API.','ranks');?></h2>
    <img src="<?php echo RANKS_URL;?>images/setting-analytics-3.png" >
  </p>

  <p>
    <h2><?php _e('4. Register NEW APP.','ranks');?></h2>
    <img src="<?php echo RANKS_URL;?>images/setting-analytics-4.png" >
  </p>

  <p>
    <h2><?php _e('5. Set CLIENT ID And CLIENT SECRET ','ranks');?></h2>
    <img src="<?php echo RANKS_URL;?>images/setting-analytics-5-1.png" >
    <img src="<?php echo RANKS_URL;?>images/setting-analytics-5-2.png" >
  </p>

<?php elseif (empty($selection)) : ?>

	<table class="form-table ranks-form-table">
		<tr>
			<th><strong><?php _e('Auth Code ','ranks');?></strong></th>
			<td>
				<input type="text" name="code" size="70" />
				<a class="button" href="javascript:void(0);" onclick="window.open('<?php echo $google_auth_url; ?>', 'activate','width=700, height=600, menubar=0, status=0, location=0, toolbar=0');"><?php _e('Acquisition Auth Code','ranks');?></a>
			</td>
		</tr>
	</table>
	<p class="submit">
		<input class="button-primary" type="submit" name="submit" value="<?php _e('Transmission','ranks');?>" />
		<input class="ranks-remove-button" type="submit" name="clear" value="<?php _e('Deletion','ranks');?>" />
	</p>

  <p>
    <h2><?php _e('6. Permission to Access NEW App','ranks');?></h2>
    <img src="<?php echo RANKS_URL;?>images/setting-analytics-6-1.png" >
    <img src="<?php echo RANKS_URL;?>images/setting-analytics-6-2.png" >
  </p>
  <p>
    <h2><?php _e('7. Input AuthCode.','ranks');?></h2>
    <img src="<?php echo RANKS_URL;?>images/setting-analytics-7.png" >
  </p>
<?php else : ?>

	<table class="form-table ranks-form-table">
		<tr>
			<th><strong><?php _e('Profile','ranks');?></strong></th>
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
		<input class="button-primary" type="submit" name="submit" value="<?php _e('Save','ranks');?>" />
		<input class="ranks-remove-button" type="submit" name="clear" value="<?php _e('Remove Settings','ranks');?>" />
	</p>

<?php endif; ?>

</form>

</div>