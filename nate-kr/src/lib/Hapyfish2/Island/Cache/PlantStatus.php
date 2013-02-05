<?php

class Hapyfish2_Island_Cache_PlantStatus
{
	public static function getLastOutIslandPeopleTime($uid)
	{
        $key = 'i:tm:opisland:' . $uid;
        $cache = Hapyfish2_Cache_Factory::getMC($uid);
        $time = $cache->get($key);
        
        if ($time === false) {
        	//$time = Hapyfish_Island_Cache_Login::getLastLoginTime($uid);
        	$time = time();
        	$cache->add($key, $time);
        }
        
        return $time;
	}
	
	public static function getLastOutPlantPeopleTime($uid, $itemId)
	{
        $key = 'i:tm:opplant:' . $uid . ':' . $itemId;
        $cache = Hapyfish2_Cache_Factory::getMC($uid);
        $time = $cache->get($key);
        
        if ($time === false) {
        	$time = self::getLastOutIslandPeopleTime($uid);
        	$cache->add($key, $time);
        }
        
        return $time;
	}
	
	public static function canOutIslandPeople($uid)
	{
		$key = 'i:lk:opisland:' . $uid;
		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		return $cache->add($key, 1, 120);
	}
	
	public static function canOutPlantPeopleOfItem($uid, $itemId)
	{
		$key = 'i:lk:opplant:' . $uid . ':' . $itemId;
		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		return $cache->add($key, 1, 120);		
	}
	
	public static function updateLastOutIslandPeopleTime($uid, $time)
	{
        $key = 'i:tm:opisland:' . $uid;
        $cache = Hapyfish2_Cache_Factory::getMC($uid);
        $cache->set($key, $time);
	}
	
	public static function updateLastOutPlantPeopleTime($uid, $itemId, $time)
	{
        $key = 'i:tm:opplant:' . $uid . ':' . $itemId;
        $cache = Hapyfish2_Cache_Factory::getMC($uid);
        $cache->set($key, $time);
	}
}