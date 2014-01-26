<?php
if ($owner_data->news['updated_at']) {
				$k=0;
				foreach ($owner_data->news['news'] as $n) {
					$k++;
					if ($k==6)
						break;
					?>
					<div class="news_item">
						<p class="news_item_title"><a href="<?=$n['link']?>"><?=$n['title']?></a></p>
						<p class="news_item_link"><?=$n['link']?></p>
						<p><span class="news_item_date"><?=$n['date']?></span> - <?=$n['body']?></p>
					</div>
				<?php } ?>
				<div class="view_btn">
					<a href="?section=news" class="button_sc medium light"><span> <?=__('See details', 'whoowns')?></span></a></span>
				</div>
			<?php } else { ?>
				<div class="news_item">
					<p class="news_item_title"><?=$owner_data->news['msg']?></p>
				</div>
			<?php } ?>
