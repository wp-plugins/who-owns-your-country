<?php

/**
 *
 * Template fragment to show the Owner's section FULL POWER NETWORK GRAPHIC
 *
 **/

?>
<script>var scroll_locked=true;	</script>
<!-- Here comes the grafo in its full glory -->
<div id="network-power-full">
	<div id="cy-loading"><img src="<?=plugins_url( '../../images/loading.gif' , __FILE__ )?>"></div>
	<div id="whoowns_tooltip"></div>
	<div id="cy-list">
		<h2><?=__("List view", "whoowns")?></h2>
		<img src="<?=plugins_url( '../../images/loading.gif' , __FILE__ )?>" style="margin:30px auto">
	</div>
	<div id="cy-full"></div>
	<div id="cy-legends"></div>
	<div id="cy-open-help-popup"><a title="<?=__('see legends and help','whoowns')?>" alt="<?=__('see legends and help','whoowns')?>" href="javascript:whoowns_open_popup('whoowns-popup');"><span class="icon-help-circled"></span></a></div>
	<div id="cy-info"></div>
	<div id="cy-info-loading"><img src="<?=plugins_url( '../../images/loading.gif' , __FILE__ )?>"></div>
</div>
<!-- end of the network map -->

<div id="whoowns-popup">
	<div class="whoowns-popup-wrap">
		<div id="whoowns-popup-content">
			<div id="whoowns-popup-close"><a href="#"><span class="icon-cancel"></span></span></a></div>
			<p class="whoowns-subtitle"><?=__("How to navigate", "whoowns")?></p>		
			<p id="whoowns-help">
				1. <?=__("Use the navigation slider on the top left to zoom in or out.", "whoowns")?> <br />
				2. <?=__("Move the enterprises or people and click on them to see more information.", "whoowns")?><br />
				3. <?=__("When you click on an enterprise or person, you'll have access to a link to open its power network.", "whoowns")?> <br />
				4. <?=__("Move the mouse over the arrows to see the relative participations among persons and enterprises.", "whoowns")?>
			</p>
			<p class="whoowns-subtitle"><?=__("Legends", "whoowns")?></p>
			<table class="whoowns-legends">
			<tr>
				<td width="30%" height="30px"><?=__("Icons", "whoowns")?></td>
				<td width="30%"><?=__("Colors", "whoowns")?></td>
				<td width="40%"><?=__("Arrows", "whoowns")?></td>
			</tr>
			<tr>
				<td><img src="<?=plugins_url( '../../images/', __FILE__ )?>private-enterprise_48.png" align="left" /><p class="label2"><?=__("Enterprise", "whoowns")?></p></td>
				<td><img src="<?=plugins_url( '../../images/', __FILE__ )?>colorRef_48.png" align="left" /><?=__("This is the reference of this network: the enterprise or person whose network we are seeing now.", "whoowns")?></td>
				<td><img src="<?=plugins_url( '../../images/', __FILE__ )?>arrowControl_48.png" align="left" /><?=__("Enterprise or person which controls the other (i.e. has more than 50% of relative participation)", "whoowns")?></td>
			</tr>
			<tr>
				<td><img src="<?=plugins_url( '../../images/', __FILE__ )?>person_48.png" align="left" /><p class="label2"><?=__("Person", "whoowns")?></p></td>
				<td><img src="<?=plugins_url( '../../images/', __FILE__ )?>colorInterChainer_48.png" align="left" /><?=__("Enterprises with inter-chain participations", "whoowns")?></td>
				<td><img src="<?=plugins_url( '../../images/', __FILE__ )?>arrowNoControl_48.png" align="left" /><?=__("The gray arrows have different widths, depending on the degree of control of one element over the other (under 50%)", "whoowns")?></td>
			</tr>
			<tr>
				<td><img src="<?=plugins_url( '../../images/', __FILE__ )?>state_48.png" align="left" /><p class="label3"><?=__("State enterprise or State institution", "whoowns")?></p></td>
				<td><img src="<?=plugins_url( '../../images/', __FILE__ )?>colorUltController_48.png" align="left" /><?=__("Ultimate controllers of their power network", "whoowns")?></td>
				<td></td>
			</tr>
			</table>
		</div>
	</div>
</div>
<div id="whoowns-popup-overlay"></div>

<?php
