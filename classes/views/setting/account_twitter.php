<div id="ranks-setting" class="wrap">

<div class="icon32" id="icon-options-general"><br></div>
<h2 style="margin-bottom: 20px;"><?php echo $title; ?></h2>

<p>
	<a href="<?php echo $this->url('index'); ?>">←<?php _e('back','ranks');?></a>
</p>

<h3><?php echo $accounts['twitter']['label']; ?> <?php _e('Setting','ranks');?></h3>

<form action="" method="post">

	<?php if ($message) echo $message; ?>

	<table class="form-table ranks-form-table">
		<tr>
			<th><strong><?php _e('Status','ranks');?></strong></th>
			<td><label><input type="checkbox" name="enable" <?php checked($accounts['twitter']['status']); ?> /> <?php _e('Enable','ranks');?></label></td>
		</tr>
	</table>
	<p class="submit">
		<input class="button-primary" type="submit" name="submit" value="<?php _e('Save','ranks');?>" />
	</p>

</form>

</div>