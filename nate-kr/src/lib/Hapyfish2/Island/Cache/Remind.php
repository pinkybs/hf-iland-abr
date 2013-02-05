<?php

class Hapyfish2_Island_Cache_Remind
{
	public static function getRemindData($uid)
	{
		$key = 'i:u:remind:' . $uid;
		$cache = Hapyfish2_Cache_Factory::getRemind($uid);
		return $cache->get($key);
	}
	
	public static function flush($uid)
	{
		$key = 'i:u:remind:' . $uid;
		$cache = Hapyfish2_Cache_Factory::getRemind($uid);
		$cache->set($key, array());
	}
	
	public static function insertRemind($uid, $remind)
    {
        $key = 'i:u:remind:' . $uid;
        $cache = Hapyfish2_Cache_Factory::getRemind($uid);
        $cache->insert($key, $remind);
    }
    
    public static function getNewRemindCount($uid)
    {
		$key = 'i:u:remind:count:' . $uid;
		$cache = Hapyfish2_Cache_Factory::getRemind($uid);
		$count = $cache->get($key);
		if ($count === false) {
			$count = 0;
		}
		
		return $count;
    }
    
	public static function incNewRemindCount($uid)
	{
		$key = 'i:u:remind:count:' . $uid;
		$cache = Hapyfish2_Cache_Factory::getRemind($uid);
		$cache->increment($key, 1);
	}
    
	public static function clearNewRemindCount($uid)
	{
		$key = 'i:u:remind:count:' . $uid;
		$cache = Hapyfish2_Cache_Factory::getRemind($uid);
		$cache->set($key, 0);
	}
}