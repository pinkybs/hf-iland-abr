<div class="operation">
	<a href="<?php h(url("server")); ?>">Server</a> | 
	<a href="<?php h(url("status")); ?>">Status</a> | 
	<a href="<?php h(url("databases")); ?>" class="current">Databases</a> [<a href="<?php h(url("createDatabase")); ?>">Create</a>] |
	<a href="<?php h(url("command")); ?>">Command</a> |
	<a href="<?php h(url("execute")); ?>">Execute</a> 
</div>

<table bgcolor="#cccccc" cellpadding="2" cellspacing="1" width="600">
	<tr>
		<th>Name</th>
		<th>Size</th>
		<th>Storage<br/>Size</th>
		<th>Data<br/>Size</th>
		<th>Index<br/>Size</th>
		<th>Collections</th>
		<th>Objects</th>
	</tr>
	<?php foreach ($dbs as $db):?>
	<tr bgcolor="#fffeee">
		<td width="120" valign="top"><a href="<?php h(url("db", array("db"=>$db["name"]))); ?>"><?php h($db["name"]);?></a></td>
		<td width="80"><?php h($db["diskSize"]);?></td>
		<td width="80"><?php h($db["storageSize"]);?></td>
		<td width="80"><?php h($db["dataSize"]);?></td>
		<td width="80"><?php h($db["indexSize"]);?></td>
		<td width="80"><?php h($db["collections"]);?></td>
		<td><?php h($db["objects"]);?></td>
	</tr>
	<?php endforeach; ?>
</table>
