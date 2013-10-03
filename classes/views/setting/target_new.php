<div id="ranks-setting" class="wrap">

<div class="icon32" id="icon-options-general"><br></div>
<h2 style="margin-bottom: 20px;"><?php echo $title; ?></h2>

<p>
	<a href="<?php echo $this->url('index'); ?>">←<?php _e('back','ranks');?></a>
</p>

<h3><?php _e('Pattern','ranks');?> <?php _e('Registration','ranks');?></h3>

<form action="" method="post">

	<?php if ($message) echo $message; ?>

	<table class="form-table ranks-form-table">
		<tr>
			<th>
				<strong><?php _e('Ranking','ranks');?></strong><br>
				<?php _e('Please set up arbitrary names.','ranks');?>
			</th>
			<td>
				<input class="regular-text" type="text" name="label" placeholder="<?php _e('Name','ranks');?>" value="<?php echo esc_attr($pattern['label']); ?>" /><br>
				<input class="regular-text" type="text" name="key" placeholder="<?php _e('Key','ranks');?>" value="<?php echo $key; ?>" />
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
				<label><input type="checkbox" name="post_type[]" value="<?php echo esc_attr($post_type); ?>" <?php checked(in_array($post_type, $pattern['post_type'])); ?> /> <?php echo $post_type_object->label; ?></label><br>
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
	</table>
	<p class="submit">
		<input class="button-primary" type="submit" name="submit" value="<?php _e('Registration','ranks');?>" />
	</p>

</form>

</div>