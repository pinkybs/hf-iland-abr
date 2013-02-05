<?php

class Hapyfish_Island_Cache_Visit
{
    /**
     * check is visit 
     *
     * @param int $uid
     * @param int $fid
     * @return bool
     */
    public static function isTodayVisit($uid, $fid)
    {
		$todayDate = date('Ymd');
		
    	$key = 'TodayVisit_' . $uid . '_' . $fid . '_' . $todayDate;
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$result = $cache->get($key);
		
        $isVisit = false;
        if ($result) {
            $isVisit = true;
        }

        return $isVisit;
    }
	
    /**
     * set visit info
     *
     * @param int $uid
     * @param int $fid
     */
    public static function setTodayVisit($uid, $fid)
    {
		$todayDate = date('Ymd');
		
    	$key = 'TodayVisit_' . $uid . '_' . $fid . '_' . $todayDate;
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$cache->add($key, 1, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_DAY);
    	
		try {
	        $dalVisit = Dal_Mongo_Visit::getDefaultInstance();
	        $isTodayVisitMongo = $dalVisit->getUserTodayVisitInfo($uid, $fid, $todayDate);
	        if (!$isTodayVisitMongo) {
	            $dalVisit->insertUserTodayVisit(array('uid' => $uid, 'fid' => $fid), $todayDate);
	            
	            $dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
	            //update user achievement,num_6
	            $dalMongoAchievement->updateUserTodayAchievementByField($uid, 'num_6', 1);
	            //update user achievement,num_6
	            $dalMongoAchievement->updateUserAchievementByField($uid, 'num_6', 1);
	        }
		}
		catch (Exception $e) {
			
		}

    }

}