<?php 
	header("content-type:text/html;charset=utf-8");
	// Report all PHP errors (bitwise 63 may be used in PHP 3)
	error_reporting(E_ALL);
	$host = '121.78.69.36';
	$username = 'worker';
	$password = 'pqnx4HVFDh';
	$dbname = 'islandv2_log_stat';
	$selDate = (int)$_GET['date'];
	$start = 20110101;
	$end = 20120101;
	if (1 == $selDate) {
		$start = 20110101;
		$end = 20110201;
	}
	else if (2 == $selDate) {
		$start = 20110201;
		$end = 20110301;
	}
	else if (3 == $selDate) {
		$start = 20110301;
		$end = 20110401;
	}
	else if (4 == $selDate) {
		$start = 20110401;
		$end = 20110501;
	}
	else if (5 == $selDate) {
		$start = 20110501;
		$end = 20110601;
	}
	else if (6 == $selDate) {
		$start = 20110601;
		$end = 20110701;
	}
	else if (7 == $selDate) {
		$start = 20110701;
		$end = 20110801;
	}
	else if (8 == $selDate) {
		$start = 20110801;
		$end = 20110901;
	}
	else if (9 == $selDate) {
		$start = 20110901;
		$end = 20111001;
	}
	else if (10 == $selDate) {
		$start = 20111001;
		$end = 20111101;
	}
	else if (11 == $selDate) {
		$start = 20111101;
		$end = 20111201;
	}
	else if (12 == $selDate) {
		$start = 20111201;
		$end = 20120101;
	}
	$conenct = mysql_connect($host,$username,$password) or die('Could not connect:'.mysql_errno());
	mysql_select_db($dbname) or die('Could not select database');
//	echo "ss";
	$sql = "select * from day_payment where log_time>=$start and log_time<$end";
	$result = mysql_query($sql,$conenct) or die('Query failed: ' . mysql_error());
//	echo "<a href='level.php'>level static</a><br>";
	echo "2011/<select id='sel' name='sel' onchange='change(this);'>\n
			<option value='0'>All\n
			<option value='1'>1月\n
			<option value='2'>2月\n
			<option value='3'>3月\n
			<option value='4'>4月\n
			<option value='5'>5月\n
			<option value='6'>6月\n
			<option value='7'>7月\n
			<option value='8'>8月\n
			<option value='9'>9月\n
			<option value='10'>10月\n
			<option value='11'>11月\n
			<option value='12'>12月\n
		  </select>";
	echo "<table>\n
		<tr>
			<th>日期</th>
			<th>交易宝石数</th>
			<th>交易笔数</th>
		</tr>";
	while ($arr = mysql_fetch_array($result,MYSQL_ASSOC)){
		echo "\t<tr>\n";
		foreach ($arr as $val){
			echo "\t\t<td style='text-align: right;'>$val</td>\n";
		}
		echo "\t</tr>\n";
	}
	echo "</table>\n";
	
	echo "<script type='text/javascript'>\n
			document.getElementById('sel').selectedIndex = $selDate;
			function change(obj){\n
			  window.location.href='payment.php?date='+obj.selectedIndex;\n
			}\n
		  </script>";
	mysql_free_result($result);
	mysql_close($conenct);
?>
