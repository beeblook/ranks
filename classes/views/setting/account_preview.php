<div class="wrap">

<div class="icon32" id="icon-options-general"><br></div>
<h2><?php echo $this->page_title; ?></h2>

<p>
	<a href="<?php echo $this->url('index'); ?>">←戻る</a>
</p>

<h3><?php echo $accounts[$account_slug]['label']; ?> プレビュー</h3>

<table class="form-table ranks-form-table">
	<tr>
		<th>
			<p><strong>データソース プレビュー</strong></p>
			<p>データソースから集計した値です。</p>
		</th>
		<td style="padding: 20px;">
<?php if (have_posts()) : ?>
			<table class="ranks-posts-table">
				<thead>
					<tr>
						<th colspan="2" style="width: auto;">投稿</th>
<?php if ($accounts[$account_slug]['status']) : ?>
						<th class="account <?php echo $account_slug; ?>"><span><?php echo $accounts[$account_slug]['label']; ?></span></th>
<?php endif; ?>
					</tr>
				</thead>
				<tbody>
<?php
$i = $prev = $rank = 0;
while(have_posts()):
	the_post();
	$i++;
	$counts = array();
	$counts[$account_slug] = (int) get_post_meta(get_the_ID(), "ranks_{$account_slug}_count", true);
	if( $counts[$account_slug] != $prev ) $rank = $i;
	$prev = $counts[$account_slug];
?>
					<tr>
						<td class="rank">
							<?php echo number_format($rank); ?>
						</td>
						<td class="post-title">
							<span><?php echo get_the_date(); ?> - <?php $pt=get_post_type_object(get_post_type()); echo $pt->label; ?></span>
							<strong><a class="row-title" href="<?php the_permalink(); ?>" target="_blank"><?php the_title() ?></a></strong>
						</td>
<?php if ($accounts[$account_slug]['status']) : ?>
						<td class="point">
							<?php echo number_format($counts[$account_slug]); ?>
						</td>
<?php endif; ?>
					</tr>
<?php endwhile; ?>
				</tbody>
			</table>
<?php else : ?>

			<p>対象の投稿が見つかりません</p>

<?php endif; ?>
		</td>
	</tr>
</table>

</div>