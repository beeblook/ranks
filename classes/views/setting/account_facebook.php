<div id="ranks-setting" class="wrap">

<div class="icon32" id="icon-options-general"><br></div>
<h2 style="margin-bottom: 20px;"><?php echo $title; ?></h2>

<p>
	<a href="<?php echo $this->url('index'); ?>">←<?php _e('back','ranks');?></a>
</p>

<h3><?php echo $accounts['facebook']['label']; ?> <?php _e('Setting','ranks');?></h3>

<form action="" method="post">

	<?php if ($message) echo $message; ?>

	<table class="form-table ranks-form-table">
		<tr>
			<th><strong><?php _e('Status','ranks');?></strong></th>
			<td><label><input type="checkbox" name="enable" <?php checked($accounts['facebook']['status']); ?> /> <?php _e('Enable','ranks');?></label></td>
		</tr>
		<tr>
			<th><strong>App ID</strong></th>
			<td><input type="text" name="app_id" value="<?php echo $accounts['facebook']['app_id'] ?>" /></td>
		</tr>
		<tr>
			<th><strong>App Secret</strong></th>
			<td><input type="text" name="app_secret" value="<?php echo $accounts['facebook']['app_secret'] ?>" /></td>
		</tr>
	</table>
	<p class="submit">
		<input class="button-primary" type="submit" name="submit" value="<?php _e('Save','ranks');?>" />
	</p>

</form>

</div>