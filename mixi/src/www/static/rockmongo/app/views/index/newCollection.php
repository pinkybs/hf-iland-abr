<h3><a href="<?php h(url("databases"));?>">Databases</a> &raquo; <a href="<?php h(url("db",array("db"=>$db)));?>"><?php h($db);?></a> &raquo; Create New Collection</h3>

<p class="error">
<?php if (isset($message)):?>
<?php h($message);?>
<script language="javascript">
window.top.frames["left"].location.reload();
</script>
<?php endif;?>
</p>

<form method="post">
Name:<br/>
<input type="text" name="name" value="<?php h($name);?>"/><br/>
Is Capped:<br/>
<input type="checkbox" name="is_capped" value="1" <?php if($isCapped):?>checked="checked"<?php endif;?>/><br/>
Size:<br/>
<input type="text" name="size" value="<?php h($size);?>"/><br/>
Max:<br/>
<input type="text" name="max" value="<?php h($max);?>"/><br/>
<input type="submit" value="Create"/>
</form>