<div class="operation">
	<a href="<?php h(url("server")); ?>" class="current">Server</a> | 
	<a href="<?php h(url("status")); ?>">Status</a> | 
	<a href="<?php h(url("databases")); ?>">Databases</a> |
	<a href="<?php h(url("command")); ?>">Command</a> |
	<a href="<?php h(url("execute")); ?>">Execute</a> 
</div>

<table bgcolor="#cccccc" cellpadding="2" cellspacing="1" width="600">
	<tr>
		<th colspan="2">Connection</th>
	</tr>
	<?php foreach ($connections as $param=>$value):?>
	<tr bgcolor="#fffeee">
		<td width="120" ><?php h($param);?></td>
		<td><?php h($value);?></td>
	</tr>
	<?php endforeach; ?>
</table>
<div class="gap"></div>

<table bgcolor="#cccccc" cellpadding="2" cellspacing="1" width="600">
	<tr>
		<th colspan="2">Web Server</th>
	</tr>
	<?php foreach ($webServers as $param=>$value):?>
	<tr bgcolor="#fffeee">
		<td width="120" ><?php h($param);?></td>
		<td><?php h($value);?></td>
	</tr>
	<?php endforeach; ?>
</table>
<div class="gap"></div>

<table bgcolor="#cccccc" cellpadding="2" cellspacing="1" width="600">
	<tr>
		<th colspan="3">Directives</th>
	</tr>
	<tr bgcolor="#fffeee">
		<th>Directive</th>
		<th>Global Value</th>
		<th>Local Value</th>
	</tr>
	<?php foreach ($directives as $param=>$value):?>
	<tr bgcolor="#fffeee">
		<td width="200" ><?php h($param);?></td>
		<td><?php h($value["global_value"]);?></td>
		<td><?php h($value["local_value"]);?></td>
	</tr>
	<?php endforeach; ?>
</table>

<div class="gap"></div>

<table bgcolor="#cccccc" cellpadding="2" cellspacing="1" width="600">
	<tr>
		<th colspan="2">Build Information ({buildinfo : 1})</th>
	</tr>
	<?php foreach ($buildInfos as $param=>$value):?>
	<tr bgcolor="#fffeee">
		<td width="120" valign="top"><?php h($param);?></td>
		<td><?php h($value);?></td>
	</tr>
	<?php endforeach; ?>
</table>

<div class="gap"></div>

