<div class="wrap">

<div class="icon32" id="icon-options-general"><br></div>
<h2><?php echo $this->page_title; ?></h2>

<p>
	<a href="<?php echo $this->url('index'); ?>">←<?php _e('back','ranks');?></a>
</p>

<h3><?php _e('集計パターン ランキング確認','ranks');?></h3>

<?php if (have_posts()) : ?>
<table class="ranks-posts-table">
	<thead>
		<tr>
			<th colspan="2" style="width: auto;"><?php _e('Post','ranks');?></th>
<?php foreach ($accounts as $account_slug => $account) : if ($account['status'] && $pattern['rates'][$account_slug] > 0) : ?>
			<th class="account <?php echo $account_slug; ?>"><span><?php echo $account['label']; ?></span></th>
<?php endif; endforeach; ?>
			<th class="account score">Score</th>
		</tr>
	</thead>
	<tbody>
<?php
while(have_posts()):
	the_post();
	$counts = array();
	foreach ($accounts as $account_slug => $account) {
		if ($account['status'] && $pattern['rates'][$account_slug] > 0) {
			$counts[$account_slug] = (int) get_post_meta(get_the_ID(), "ranks_{$account_slug}_count", true);
		}
	}
	$score = (int) get_post_meta(get_the_ID(), $key, true);
?>
		<tr>
			<td class="rank">
				<?php the_rank(); ?>
			</td>
			<td class="post-title">
				<?php the_time(__('m/d/Y','ranks')); ?> <span class="description"><?php $pt=get_post_type_object(get_post_type()); echo $pt->label; ?></span>
				<strong><a class="row-title" href="<?php the_permalink(); ?>" target="_blank"><?php the_title() ?></a></strong>
			</td>
<?php foreach ($counts as $account_slug => $count) : ?>
			<td class="point">
				<?php echo number_format($count); ?>
			</td>
<?php endforeach; ?>
			<td class="point score">
				<?php echo number_format($score); ?>
			</td>
		</tr>
<?php endwhile; ?>
	</tbody>
</table>
<?php else : ?>

<p>対象の投稿が見つかりません</p>

<?php endif; ?>

</div>