
<div style="padding:10px;margin:50px auto;;width:300px;border:1px #ccc solid">
<?php if (isset($message)):?><p class="error"><?php h($message); ?></p><?php endif;?>
	<form method="post">
	<table>
		<tr>
			<td>Host:</td>
			<td><select name="host" style="width:150px">
			<?php foreach ($servers as $index => $server) : ?>
			<option value="<?php h($index);?>"><?php h($server["host"]); ?></option>
			<?php endforeach; ?>
			</select></td>
		</tr>
		<tr>
			<td>Admin:</td>
			<td><input type="text" name="username" value="<?php echo $username;?>" style="width:150px"/></td>
		</tr>
		<tr>
			<td>Password:</td>
			<td><input type="password" name="password" style="width:150px"/></td>
		</tr>
		<tr>
			<td colspan="2"><input type="submit" value="Login and Rock"/></td>
		</tr>
		<tr>
			<td colspan="2">
				<div class="gap"></div>
				<ul>
					<li>You may change your username and password in index.php.</li>
					<li>Powered by RockMongo v<?php h(ROCK_MONGO_VERSION);?>, <a href="http://code.google.com/p/rock-php/downloads/list" target="_blank">check out new version here.</a></li>
				</ul>
				</td>
		</tr>
	</table>
	</form>
</div>