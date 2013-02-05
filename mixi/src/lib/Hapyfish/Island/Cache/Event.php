<?php

class Hapyfish_Island_Cache_Event
{
    /**
     * get is first of today
     *
     * @param int $uid
     * @return bool
     */
    public static function isFirstOfDay($uid)
    {
    	$today = date('Ymd');
    	$key = 'FirstOfDay_' . $uid . '_' . $today;
    	$cache = Hapyfish_Cache_Memcached::getInstance();
    	$result = $cache->get($key);
    	
        $isFirst = true;
        if ($result) {
        	$isFirst = false;
        }

        return $isFirst;
    }
    
    /**
     * set first of today
     *
     * @param int $uid
     */
    public static function setFirstOfDay($uid)
    {
    	$today = date('Ymd');
    	$key = 'FirstOfDay_' . $uid . '_' . $today;
    	$cache = Hapyfish_Cache_Memcached::getInstance();
    	$cache->add($key, 1, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_DAY);
    }

}