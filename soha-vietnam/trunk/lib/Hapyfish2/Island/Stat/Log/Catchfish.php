<?php

class Hapyfish2_Island_Stat_Log_Catchfish
{
	public static function handle($day, $file)
	{
		$content = file_get_contents($file);
		if (empty($content)) {
			info_log('no data', 'stat.log.catch.fish.err');
			return;
		}
		
		$temp = explode("\n", $content);
		
		$allCount = 0;
		$catchInfo = array('allCount' => 0,
						  'fishId1' => 0,
						  'fishId2' => 0,
						  'fishId3' => 0,
						  'fishId4' => 0,
						  'fishId5' => 0,
						  'fishId6' => 0,
						  'fishId7' => 0,
						  'fishId8' => 0,
						  'fishId9' => 0,
						  'fishId10' => 0,
						  'fishId11' => 0,
						  'fishId12' => 0,	
						  'fishId13' => 0,
						  'fishId14' => 0,
						  'fishId15' => 0,
						  'fishId16' => 0,
						  'fishId17' => 0,
						  'fishId18' => 0,
						  'fishId19' => 0,
						  'fishId20' => 0,
						  'fishId21' => 0,
						  'fishId22' => 0,
						  'fishId23' => 0,
						  'fishId24' => 0,
						  'fishId25' => 0,
						  'fishId26' => 0);
		
		foreach ($temp as $line) {
			if (empty($line)) {
				continue;
			}
			
			$r = explode("\t", $line);
			
			//array($uid, $fishId, $levelId)
			$uid= $r[2];
			$fishId = $r[3];
			$levelId = $r[4];
			
			$catchInfo['allCount'] ++;
			if($fishId == 1) {
				$catchInfo['fishId1'] ++;
			} elseif(in_array($fishId,array(2,8))) {
				$catchInfo['fishId2'] ++;
			} elseif($fishId == 3) {
				$catchInfo['fishId3'] ++;
			} elseif(in_array($fishId,array(4,10,15))) {
				$catchInfo['fishId4'] ++;
			} elseif(in_array($fishId,array(5,11,16))) {
				$catchInfo['fishId5'] ++;
			} elseif(in_array($fishId,array(6,12,19,24))) {
				$catchInfo['fishId6'] ++;
			} elseif(in_array($fishId,array(7,14))) {
				$catchInfo['fishId7'] ++;
			} elseif($fishId == 9) {
				$catchInfo['fishId8'] ++;
			} elseif($fishId == 13) {
				$catchInfo['fishId9'] ++;
			} elseif($fishId == 17) {
				$catchInfo['fishId10'] ++;
			} elseif(in_array($fishId,array(18,23))) {
				$catchInfo['fishId11'] ++;
			} elseif($fishId == 20) {
				$catchInfo['fishId12'] ++;
			} elseif($fishId == 21) {
				$catchInfo['fishId13'] ++;
			} elseif($fishId == 22) {
				$catchInfo['fishId14'] ++;
			} elseif($fishId == 25) {
				$catchInfo['fishId15'] ++;
			} elseif(in_array($fishId,array(26,31))) {
				$catchInfo['fishId16'] ++;
			} elseif($fishId == 27) {
				$catchInfo['fishId17'] ++;
			} elseif($fishId == 28) {
				$catchInfo['fishId18'] ++;
			} elseif($fishId == 29) {
				$catchInfo['fishId19'] ++;
			} elseif(in_array($fishId,array(30,32))) {
				$catchInfo['fishId20'] ++;
			} elseif($fishId == 33) {
				$catchInfo['fishId21'] ++;
			} elseif($fishId == 34) {
				$catchInfo['fishId22'] ++;
			} elseif($fishId == 35) {
				$catchInfo['fishId23'] ++;
			} elseif($fishId == 36) {
				$catchInfo['fishId24'] ++;
			} elseif($fishId == 37) {
				$catchInfo['fishId25'] ++;
			} elseif($fishId == 38) {
				$catchInfo['fishId26'] ++;
			}
		}
		
		$catchInfo['create_time'] = $day;
        $dalCatchisland = Hapyfish2_Island_Stat_Dal_Catchfish::getDefaultInstance();
        $dalCatchisland->insert($catchInfo);
        
		return $catchInfo;
	}
	public static function handleProduct($day, $file)
	{
		$content = file_get_contents($file);
		if (empty($content)) {
			info_log('no data', 'stat.log.catch.product.err');
			return;
		}
		
		$temp = explode("\n", $content);
		$allCount = 0;
		$count = 0;
		
		foreach ($temp as $line) {
			if (empty($line)) {
				continue;
			}	
			$r = explode("\t", $line);
			$count++;
		}
		 $dalCatchisland = Hapyfish2_Island_Stat_Dal_Catchfish::getDefaultInstance();	
		 $dalCatchisland->update($day,$count);
		 return $count;					
	}
	public static function handleUserNum($day, $file)
	{
		$content = file_get_contents($file);
		if (empty($content)) {
			info_log('no data', 'stat.log.catch.user.err');
			return;
		}
		
		$temp = explode("\n", $content);
		$count = 0;
		$uidArr = array();
		foreach ($temp as $line) {
			if (empty($line)) {
				continue;
			}	
			$r = explode("\t", $line);
			$uid= $r[2];
			if(!isset($uidArr[$uid])) {
				$uidArr[$uid]=1;
				$count++;
			}
			
		}
		 $dalCatchisland = Hapyfish2_Island_Stat_Dal_Catchfish::getDefaultInstance();	
		 $dalCatchisland->updateUserNum($day,$count);
		 return $count;					
	}	
	public static function handleCoinAndCard($day, $file)
	{
		$content = file_get_contents($file);
		if (empty($content)) {
			info_log('no data', 'stat.log.catch.coinandcard.err');
			return;
		}
		
		$temp = explode("\n", $content);
		$coin = 0;
		$card = 0;
		foreach ($temp as $line) {
			if (empty($line)) {
				continue;
			}	
			$r = explode("\t", $line);
			$uid= $r[2];
			$num = $r[3];
			$type = $r[4];
			if($type == 1) {
				$coin = $coin+$num;
			}elseif($type == 2) {
				$card = $card+$num;
			}
			
		}
		 $dalCatchisland = Hapyfish2_Island_Stat_Dal_Catchfish::getDefaultInstance();	
		 $dalCatchisland->updateCoinAndCard($day, $coin, $card);
		 return array($coin, $card);					
	}	
}