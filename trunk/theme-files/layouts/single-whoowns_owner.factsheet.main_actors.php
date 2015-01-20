<table>
				<tr>
  					<?php if ($owner_data->main_actors['composition']) { ?>
   						<th><?=__('Controllers', 'whoowns')?>:</th>
    				<?php } ?>
    				<?php if ($owner_data->main_actors['participation']) { ?>
						<th><?=__('Participations', 'whoowns')?>:</th>
					<?php } ?>
  				</tr>
  				<tr>
  					<?php if ($owner_data->main_actors['composition']) { ?>
    					<td>
    						<ul class="whoowns_highlights whoowns_participations">
    							<?php
    							if (!is_array($owner_data->main_actors['composition']))
    								$owner_data->main_actors['composition']=array($owner_data->main_actors['composition']);
    							foreach ($owner_data->main_actors['composition'] as $c) {
    								?><li class="whoowns-main-actors"><a href="<?=$c->link?>"><?=$c->name?></a></li><?php
    							}
    							?>
    						</ul>
    					</td>
    				<?php } ?>
    				<?php if ($owner_data->main_actors['participation']) { ?>
   						<td>
    						<ul class="whoowns_highlights whoowns_participations">
    							<?php
    							if (!is_array($owner_data->main_actors['participation']))
    								$owner_data->main_actors['participation']=array($owner_data->main_actors['participation']);
    							foreach ($owner_data->main_actors['participation'] as $c) {
    								?><li class="whoowns-main-actors"><a href="<?=$c->link?>"><?=$c->name?></a></li><?php
    							}
    							?>
    						</ul>
   						</td>
    				<?php } ?>
				</tr>
			</table>
