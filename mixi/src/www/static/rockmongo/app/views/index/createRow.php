<h3><a href="<?php h(url("databases"));?>">Databases</a> &raquo; <a href="<?php h(url("db",array("db"=>$db)));?>"><?php h($db);?></a> &raquo; <a href="<?php h(url("collection", array( "db"=>$db,"collection"=>$collection )));?>"><?php h($collection)?></a> &raquo; Create Row</h3>

<p class="error">
<?php if (isset($message)):h($message);endif; ?>
</p>

<form method="post">
Data:<br/>
<textarea rows="20" cols="60" name="data"><?php echo x("data") ?></textarea><br/>
<input type="submit" value="Save"/>
</form>

Data must be a valid PHP array, just like:
<blockquote>
<pre>
array (
	'value1' => 1,
	'value2' => 2,
	...
);
</pre>
</blockquote>