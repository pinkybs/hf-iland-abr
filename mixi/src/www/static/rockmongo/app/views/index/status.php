<div class="operation">
	<a href="<?php h(url("server")); ?>">Server</a> | 
	<a href="<?php h(url("status")); ?>" class="current">Status</a> | 
	<a href="<?php h(url("databases")); ?>">Databases</a> |
	<a href="<?php h(url("command")); ?>">Command</a> |
	<a href="<?php h(url("execute")); ?>">Execute</a> 
</div>

<table bgcolor="#cccccc" cellpadding="2" cellspacing="1" width="600">
	<tr>
		<th colspan="2">Server Status ({serverStatus : 1})</th>
	</tr>
	<?php foreach ($status as $param=>$value):?>
	<tr bgcolor="#fffeee">
		<td width="120" valign="top"><?php h($param);?></td>
		<td><?php h($value);?></td>
	</tr>
	<?php endforeach; ?>
</table>
