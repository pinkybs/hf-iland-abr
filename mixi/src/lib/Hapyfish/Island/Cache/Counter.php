<?php

class Hapyfish_Island_Cache_Counter
{
	public static function getNewMiniFeedCount($uid)
	{
		$key = 'NewMiniFeedCount_' . $uid;
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$count = $cache->get($key);
		
		if ($count === false) {
			$count = 0;
			$cache->add($key, $count, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
		}
		
		return $count;				
	}
	
	public static function incNewMiniFeedCount($uid, $count = 1)
	{
		$key = 'NewMiniFeedCount_' . $uid;
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$cache->increment($key, $count);
	}
	
	public static function clearNewMiniFeedCount($uid)
	{
		$key = 'NewMiniFeedCount_' . $uid;
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$cache->replace($key, 0, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
	}
	
	public static function getNewRemindCount($uid)
	{
		$key = 'NewRemindCount_' . $uid;
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$count = $cache->get($key);
		
		if ($count === false) {
			$count = 0;
			$cache->add($key, $count, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
		}
		
		return $count;				
	}
	
	public static function incNewRemindCount($uid, $count = 1)
	{
		$key = 'NewRemindCount_' . $uid;
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$cache->increment($key, $count);
	}
	
	public static function clearNewRemindCount($uid)
	{
		$key = 'NewRemindCount_' . $uid;
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$cache->replace($key, 0, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
	}

}