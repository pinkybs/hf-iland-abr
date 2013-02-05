<div class="operation">
	<a href="<?php h(url("server")); ?>">Server</a> | 
	<a href="<?php h(url("status")); ?>">Status</a> | 
	<a href="<?php h(url("databases")); ?>">Databases</a> [<a href="<?php h(url("createDatabase")); ?>" class="current">Create</a>] |
	<a href="<?php h(url("command")); ?>">Command</a> |
	<a href="<?php h(url("execute")); ?>">Execute</a> 
</div>

<?php if(isset($error)):?>
<p class="error"><?php h($error);?></p>
<?php endif;?>
<?php if(isset($message)):?>
<p class="message"><?php h($message);?></p>
<?php endif;?>

<?php if (!empty($_POST)):?>
<script language="javascript">
window.top.frames["left"].location.reload();
</script>
<?php endif;?>

<form method="post">
Name:<br/>
<input type="text" name="name" value="<?php h(x("name"));?>"/><br/>
<input type="submit" value="Create"/>
</form>