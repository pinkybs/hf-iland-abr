<?php

class Hapyfish2_Island_Stat_Log_Openisland
{
	public static function handle($day, $time, $file)
	{
		$content = file_get_contents($file);
		if (empty($content)) {
			info_log('no data', 'stat.log.tutorial.err');
			return;
		}
		
		$temp = explode("\n", $content);
		
		$allCount = 0;
		$openInfo = array('allCount' => 0,
						  'island2' => 0,
						  'island2Coin' => 0,
						  'island2Gold' => 0,
						  'island3' => 0,
						  'island3Coin' => 0,
						  'island3Gold' => 0,
						  'island4' => 0,
						  'island4Coin' => 0,
						  'island4Gold' => 0,);
		
		foreach ($temp as $line) {
			if (empty($line)) {
				continue;
			}
			
			$r = explode("\t", $line);
			
			//array($uid, $islandId, $priceTypeLog, $price)
			$uid= $r[2];
			$islandId = $r[3];
			$priceType = $r[4];
			$price = $r[5];
			
			$openInfo['allCount'] ++;
			if ( $islandId == 2 ) {
				$openInfo['island2'] ++;
				if ( $priceType == 1 ) {
					$openInfo['island2Coin'] ++;
				}
				else {
					$openInfo['island2Gold'] ++;
				}
			}
			else if ( $islandId == 3 ) {
				$openInfo['island3'] ++;
				if ( $priceType == 1 ) {
					$openInfo['island3Coin'] ++;
				}
				else {
					$openInfo['island3Gold'] ++;
				}
			}
			else {
				$openInfo['island4'] ++;
				if ( $priceType == 1 ) {
					$openInfo['island4Coin'] ++;
				}
				else {
					$openInfo['island4Gold'] ++;
				}
			}
		}
		
		$openInfo['create_time'] = $day;
        $dalOpenisland = Hapyfish2_Island_Stat_Dal_Openisland::getDefaultInstance();
        $dalOpenisland->insert($openInfo);
        
		return $openInfo;
	}
	
}