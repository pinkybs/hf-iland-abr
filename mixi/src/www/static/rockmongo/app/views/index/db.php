<h3><a href="<?php h(url("databases"));?>">Databases</a> &raquo; <?php h($db);?></h3>

<div class="operation">
	<strong>Statistics</strong> | 
	<a href="<?php h(url("newCollection",array("db"=>$db))); ?>">New Collection</a> | 
	<a href="<?php h(url("dropDatabase", array("db"=>$db))); ?>" onclick="return window.confirm('Caution:are you sure to drop database <?php h($db);?>?All data in the db will be losed!');">Drop</a> | 
	<a href="<?php h(url("command",array("db"=>$db))); ?>">Command</a> | 
	<a href="<?php h(url("execute",array("db"=>$db))); ?>">Execute</a>
</div>

<table bgcolor="#cccccc" cellpadding="2" cellspacing="1" width="600">
	<?php foreach ($stats as $param=>$value):?>
	<tr bgcolor="#fffeee">
		<td width="120" valign="top"><?php h($param);?></td>
		<td><?php h($value);?></td>
	</tr>
	<?php endforeach; ?>
</table>