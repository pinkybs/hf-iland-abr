<div class="operation">
	<a href="<?php h(url("server")); ?>">Server</a> | 
	<a href="<?php h(url("status")); ?>">Status</a> | 
	<a href="<?php h(url("databases")); ?>">Databases</a> |
	<a href="<?php h(url("command")); ?>" class="current">Command</a> |
	<a href="<?php h(url("execute")); ?>">Execute</a> 
</div>

<a href="http://www.mongodb.org/display/DOCS/List+of+Database+Commands" target="_blank">&raquo; List of database commands</a> 

<?php if(isset($message)):?>
<p class="error"><?php h($message);?></p>
<?php endif;?>

<form method="post">
<textarea name="command" rows="5" cols="60"><?php h(x("command"));?></textarea>
<br/>
DB:
<select name="db">
<?php foreach ($dbs as $value):?>
<option value="<?php h($value["name"]);?>" <?php if(xn("db")==$value["name"]):?>selected="selected"<?php endif;?>><?php h($value["name"]);?></option>
<?php endforeach;?>
</select> 
Format:<select name="format">
<?php foreach (array("json" => "JSON", "array" => "Array") as $param=>$value):?>
<option value="<?php h($param);?>" <?php if(x("format")==$param):?>selected="selected"<?php endif;?>><?php h($value);?></option>
<?php endforeach;?>
</select> 
<br/>
<input type="submit" value="Execute Command"/> 
</form>

<?php if(isset($ret)):?>

<div style="border:2px #ccc solid;margin-bottom:5px;background-color:#eeefff">
Response from server:
	<div style="margin-top:5px">
		<?php h($ret);?>
	</div>
</div>
<?php endif; ?>