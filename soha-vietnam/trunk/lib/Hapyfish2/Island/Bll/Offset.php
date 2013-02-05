<?php

class Hapyfish2_Island_Bll_Offset
{
    
	public static function offsetUserCion()
	{
		for($i=0;$i<=7;$i++){
			for($j=0;$j<=9;$j++){
				$db[$i][]= 8*$j + $i;
			}
		}
		$num = 0;
		$dal = Hapyfish2_Island_Dal_Offset::getDefaultInstance();
		$co = new Hapyfish2_Island_Bll_Compensation();
		foreach($db as $k => $v){
			foreach($v as $k1 => $v1){
				$list = $dal->getUserCostCoin($v1);
				if($list){
					foreach($list as $key => $value){
						$title = '手动扩岛金币退还';
						$co->setCoin($value['total']);
						$ok = $co->sendOne($value['uid'], $title);
						if($ok){
							$dal->delUserCoinLog($value['uid']);
							info_log($value['uid'].':'.$value['total'], 'kuodaobuchang');
							$num +=1;
						}
					}
				}
			}
		}
		return $num;
	}
}
