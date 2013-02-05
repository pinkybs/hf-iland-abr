<?php

class Hapyfish2_Island_Bll_StarFish
{
	public static function add($uid, $num, $summary, $time = null)
	{
		$ok = Hapyfish2_Island_HFC_User::incUserStarFish($uid, $num, true);
		if ($ok) {
			if (!$time) {
				$time = time();
			}
			$info = array(
				'uid' => $uid,
				'change' => $num,
				'summary' => $summary,
				'create_time' => $time
			);
			
			try {
				$dalLog = Hapyfish2_Island_Dal_StarFishLog::getDefaultInstance();
				$dalLog->insert($uid, $info);
			} catch (Exception $e) {
				
			}
		}
		
		return $ok;
	}
	
	public static function consume($uid, $num, $summary, $time = null)
	{
		$ok = Hapyfish2_Island_HFC_User::decUserStarFish($uid, $num, true);
		if ($ok) {
			if (!$time) {
				$time = time();
			}
			$info = array(
				'uid' => $uid,
				'change' => -$num,
				'summary' => $summary,
				'create_time' => $time
			);
			
			try {
				$dalLog = Hapyfish2_Island_Dal_StarFishLog::getDefaultInstance();
				$dalLog->insert($uid, $info);
			} catch (Exception $e) {
				
			}
		}
		
		return $ok;
	}
	

}