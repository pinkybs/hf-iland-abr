	<div class="top">
		<div class="left">mongodb://<?php h($server["host"]);?>:<?php h($server["port"]);?> </div>
		<div class="right"><?php h($admin);?> |  <a href="<?php h($logoutUrl);?>" target="_top">Logout</a> | <a href="<?php h(url("about")); ?>" target="right">RockMongo v<?php h(ROCK_MONGO_VERSION);?></a></div>
		<div class="clear"></div>
	</div>
