<div style="border-right:1px #ccc solid;height:100%;background-color:#eeefff;">
	<div style="margin-left:30px;margin-bottom:7px"><a href="<?php h(url("server"));?>" target="right">Server</a></div>
	<div style="margin-left:30px;margin-bottom:2px">Databases:</div>
	<ul class="dbs">
		<?php foreach ($dbs as $db) : ?>
		<li><a href="<?php echo $baseUrl;?>&db=<?php h($db["name"]);?>" <?php if ($db["name"] == x("db")): ?>style="font-weight:bold"<?php endif;?> onclick="window.top.frames['right'].location='<?php h(url("db",array("db"=>$db["name"])));?>'"><?php echo $db["name"];?></a><?php if($db["collectionCount"]>0):?> (<?php h($db["collectionCount"]); ?>)<?php endif;?>
			<ul>
				<?php if($db["name"] == x("db")): ?>
					<?php if (!empty($tables)):?>
						<?php foreach ($tables as $table) :?>
						<li><a href="<?php h(url("collection", array( "db" => $db["name"], "collection" => $table ))); ?>" target="right"><?php h($table);?></a></li>
						<?php endforeach; ?>
					<?php else:?>
						<li>No collections yet</li>
					<?php endif;?>
				<?php endif; ?>
				<?php if ($db["name"] == x("db")):?>
				<li><a href="<?php h(url("newCollection", array( "db" => $db["name"] ))); ?>" target="right">Create &raquo;</a></li>
				<?php endif;?>
			</ul>
		</li>
		<?php endforeach; ?>
	</ul>
	
</div>