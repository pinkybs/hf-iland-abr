<?php

class Hapyfish2_Island_Cache_Visit
{
    public static function dailyVisit($uid, $fid)
    {
    	$nowTime = time();
    	
    	//1315929600->2011-09-14 00:00:00
    	if ( $nowTime < 1315929600 ) {
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
        else {
	        $today = date('Ymd');
	        
	        $key = 'i:u:dlyvisit:' . $uid . ':' . $fid;
	        $cache = Hapyfish2_Cache_Factory::getMC($uid);
	        $data = $cache->get($key);
	        
	        if ( $data == $today ) {
	        	return;
	        }
	        
	        Hapyfish2_Island_HFC_Achievement::updateUserAchievementByField($uid, 'num_6', 1);
	        Hapyfish2_Island_HFC_AchievementDaily::updateUserAchievementDailyByField($uid, 'num_6', 1);
	        
	        $cache->set($key, $today);
	        
	        try {
	            //task id 3027,task type 6
	            Hapyfish2_Island_Bll_Task::checkTask($uid, 3027);
	        } catch (Exception $e) {
	        }
        }
    }
	/**
	 * @获取每日接待游客数
	 * @param int $uid
	 * @return int
	 */
    public static function getVisitorNum($uid)
    {
		$key = 'i:u:visitor:num:' . $uid;
		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		$num = $cache->get($key);

		if ($num === false) {
			//每日接待游客数,每天的23:59:59清空
			$logDate = date('Y-m-d');
			$dtDate = $logDate . ' 23:59:59';
			$endTime = strtotime($dtDate);

			$num = 0;

			$cache->set($key, $num, $endTime);
		}

		return $num;
    }

	/**
	 * @每次接待游客计入缓存
	 * @param int $uid
	 * @param int $addNum
	 */
	public static function addAccVisitorNum($uid, $addNum)
	{
		$key = 'i:u:visitor:num:' . $uid;
		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		$num = $cache->get($key);

		$addNum += $num;

		//每日接待游客数,每天的23:59:59清空
		$logDate = date('Y-m-d');
		$dtDate = $logDate . ' 23:59:59';
		$endTime = strtotime($dtDate);

		$cache->set($key, $addNum, $endTime);
	}   
}