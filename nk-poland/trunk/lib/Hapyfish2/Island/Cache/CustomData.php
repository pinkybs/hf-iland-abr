<?php

class Hapyfish2_Island_Cache_CustomData
{
	public static function get($uid, $key)
	{
		$mckey = 'i:ctmdata:' . $uid . ':' . $key;
		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		return $cache->get($mckey);
	}
	
	public static function set($uid, $key, $data, $time = 86400)
	{
		$mckey = 'i:ctmdata:' . $uid . ':' . $key;
		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		return $cache->set($mckey, $data, $time);
	}
    
}