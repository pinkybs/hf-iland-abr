<?php

class Hapyfish2_Island_Cache_Activity
{
    public static function getLastSendTime($uid)
    {
		$key = 'i:u:activity:' . $uid;
        $cache = Hapyfish2_Cache_Factory::getMC($uid);
		$data = $cache->get($key);
		if ($data === false) {
			$data = 0;
		}
		
		return $data;
    }
    
    public static function setLastSendTime($uid, $time)
    {
		$key = 'i:u:activity:' . $uid;
        $cache = Hapyfish2_Cache_Factory::getMC($uid);
		$cache->set($key, $time);
    }
    
}