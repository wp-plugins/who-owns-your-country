<?php

/**
 *
 * Template fragment to show the Owner's section IN THE MEDIA (related news)
 *
 **/

?>
<header class="archive-header">
	<h1 class="archive-title"><?php printf( __( 'News related to the power network of %s', 'whoowns' ), get_the_title() ); ?></h1>
</header><!-- .archive-header -->

<div class="width_articles">
<?php
	if ($owner_data->news['updated_at']) {
		foreach ($owner_data->news['news'] as $n) {
			$where = ($where=='one_half')
				? 'two_half_last'
				: 'one_half';
			?>
			<div class="news_article_full <?=$where?>">
				<header>
				<h2><a href="<?=$n['link']?>" title="<?=$n['title']?>" rel="bookmark"><?=$n['title']?></a></h2>
				<div class="meta">
					<div class="mfield date"><?=$n['date']?></div>
				</div>
				</header>
    			    <section class="summary">
					<p><?=$n['body']?></p><p><a href="<?=$n['link']?>"></a></p>
					<div class="readon"><a href="<?=$n['link']?>"><?=__("Read more", "whoowns")?></a></div>
				</section>
			</div>
		<?php } ?>
	<?php } else { ?>
		<div class="news_article_full">
			<h3><?=$owner_data->news['msg']?></h3>
		</div>
	<?php } ?>
	</div>
<?php
