<?php

class Hapyfish_Island_Cache_Login
{
	public static function getLastLoginTime($uid)
	{
		$key = '2LastLoginTime_' . $uid;
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$time = $cache->get($key);
		
		if ($time === false) {
			$db = Hapyfish_Island_Dal_Login::getDefaultInstance();
			$time = $db->getLastLoginTime($uid);
			$cache->add($key, $time, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
		}
		
		return $time;				
	}
	
	public static function updateLastLoginTime($uid, $time)
	{
		try {
			$db = Hapyfish_Island_Dal_Login::getDefaultInstance();
			$db->updateLastLoginTime($uid, $time);
			
			$key = '2LastLoginTime_' . $uid;
			$cache = Hapyfish_Cache_Memcached::getInstance();
			$cache->replace($key, $time, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
				
		} catch (Exception $e) {
			
		}
	}
	
	public static function getTodayLoginCount($uid, $todayUnixTime)
	{
		$key = '2TodayLoginCount_' . $uid . '_' . $todayUnixTime;
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$count = $cache->get($key);
		
		if ($count === false) {
			$db = Hapyfish_Island_Dal_Login::getDefaultInstance();
			$count = $db->getTodayLoginCount($uid);
			$cache->add($key, $count, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_DAY);
		}
		
		return $count;		
	}
	
	public static function updateTodayLoginCount($uid, $todayUnixTime, $count)
	{
		try {
			$db = Hapyfish_Island_Dal_Login::getDefaultInstance();
			$db->updateTodayLoginCount($uid, $count);
			
			$key = '2TodayLoginCount_' . $uid . '_' . $todayUnixTime;
			$cache = Hapyfish_Cache_Memcached::getInstance();
			$cache->replace($key, $count, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_DAY);
				
		} catch (Exception $e) {
			
		}
	}
	
	public static function getActivityLoginCount($uid)
	{
		$key = '2ActivityLoginCount_' . $uid;
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$count = $cache->get($key);
		
		if ($count === false) {
			$db = Hapyfish_Island_Dal_Login::getDefaultInstance();
			$count = $db->getActivityLoginCount($uid);
			$cache->add($key, $count, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
		}
		
		return $count;	
	}
	
	public static function updateActivityLoginCount($uid)
	{
		try {
			$db = Hapyfish_Island_Dal_Login::getDefaultInstance();
			$db->updateActivityLoginCount($uid, $count);
			
			$key = '2ActivityLoginCount_' . $uid;
			$cache = Hapyfish_Cache_Memcached::getInstance();
			$cache->replace($key, $count, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
				
		} catch (Exception $e) {
			
		}		
	}
	
	public static function updateLoginInfo($uid, $loginInfo, $todayUnixTime)
	{
		try {
			$db = Hapyfish_Island_Dal_Login::getDefaultInstance();
			$db->updateLoginInfo($uid, $loginInfo);
			
			$cache = Hapyfish_Cache_Memcached::getInstance();
			
			if (isset($loginInfo['last_login_time'])) {
				$key = '2LastLoginTime_' . $uid;
				$cache->replace($key, $loginInfo['last_login_time'], Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
			}
			
			if (isset($loginInfo['today_login_count'])) {
				$key = '2TodayLoginCount_' . $uid . '_' . $todayUnixTime;
				$cache->replace($key, $loginInfo['today_login_count'], Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
			}

			if (isset($loginInfo['activity_login_count'])) {
				$key = '2ActivityLoginCount_' . $uid;
				$cache->replace($key, $loginInfo['activity_login_count'], Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
			}
				
		} catch (Exception $e) {
			
		}		
	}
	
    /**
     * check user show view news
     *
     * @param int $uid
     */
    public static function showViewNews($uid)
    {
        $showViewNews = false;
        
        $hasViewCount = self::hasViewCount($uid);
        if ( $hasViewCount < 7 ) {
        	$todayHasView = self::todayHasView($uid);
        	if ( $todayHasView === false ) {
        		self::updateViewCount($uid);
        		$showViewNews = true;
        	}
        }
        
        return $showViewNews;
    }
    
    /**
     * get has view news count
     *
     * @param int $uid
     */
    public static function hasViewCount($uid)
    {
		$key = 'hasViewCount_' . $uid;
		$cache = Hapyfish_Cache_Memcached::getInstance();
        $result = $cache->get($key);

        if ( $result === false ) {
        	$hasViewCount = 0;
        }
        else {
        	$hasViewCount = $result;
        }
        
        return $result;
    }
    
    /**
     * check today has view news
     *
     * @param int $uid
     */
    public static function todayHasView($uid)
    {
		$todayDate  = date('Ymd');
    	$key = 'todayHasView' . $uid . '_' . $todayDate;
		$cache = Hapyfish_Cache_Memcached::getInstance();
        $result = $cache->get($key);
    	
        if ( $result === false ) {
        	$cache->add($key, 1, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_DAY);
	        $todayHasView = false;
        }
        else {
            $todayHasView = true;
        }
        
        return $todayHasView;
    }
    
    /**
     * update has view news count
     *
     * @param int $uid
     */
    public static function updateViewCount($uid)
    {
		$key = 'hasViewCount_' . $uid;
		$cache = Hapyfish_Cache_Memcached::getInstance();
        $result = $cache->get($key);

        if ( $result === false ) {
        	$hasViewCount = 0;
        	$cache->add($key, 1, Hapyfish_Cache_Memcached::LIFE_TIME_MAX);
        }
        else {
        	$hasViewCount = $result;
        	$cache->increment($key, 1);
        }
        
        return $hasViewCount;
    }

}