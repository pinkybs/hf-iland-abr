<?php

class Hapyfish2_Island_Cache_Visit
{
    public static function dailyVisit($uid, $fid)
    {
		$today = date('Ymd');
		
		$key = 'i:u:dlyvisit:' . $uid . ':' . $fid . ':' . $today;
		
        $cache = Hapyfish2_Cache_Factory::getMC($uid);
        if ($cache->add($key, 1, 86400)) {
        	Hapyfish2_Island_HFC_Achievement::updateUserAchievementByField($uid, 'num_6', 1);
        	Hapyfish2_Island_HFC_AchievementDaily::updateUserAchievementDailyByField($uid, 'num_6', 1);
        
	        try {
				//task id 3027,task type 6
				Hapyfish2_Island_Bll_Task::checkTask($uid, 3027);
	        } catch (Exception $e) {
	        }
        }
    }
    
}