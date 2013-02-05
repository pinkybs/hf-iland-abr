<?php

class Hapyfish_Island_Cache_Background
{
	
	public static function getUsingBackground($uid)
	{
		$key = 'UsingBackground_' . $uid;
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$backgrounds = $cache->get($key);
		if ($backgrounds === false) {
			//load from database
			$db = Hapyfish_Island_Dal_Background::getDefaultInstance();
			$backgrounds = $db->getUsingBackground($uid);
			if ($backgrounds) {
				$cache->add($key, $backgrounds, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
			}
		}
		
		return $backgrounds;
	}
	
    public static function cleanUsingBackground($uid)
    {
 		$key = 'UsingBackground_' . $uid;
		$cache = Hapyfish_Cache_Memcached::getInstance();
		return $cache->delete($key);   	
    }

}