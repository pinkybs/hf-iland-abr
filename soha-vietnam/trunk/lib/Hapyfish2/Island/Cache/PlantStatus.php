<?php

class Hapyfish2_Island_Cache_PlantStatus
{
	public static function getLastOutIslandPeopleTime($uid, $islandId)
	{
        $key = 'i:tm:opisland:' . $uid . ':' . $islandId;
        $cache = Hapyfish2_Cache_Factory::getMC($uid);
        $time = $cache->get($key);
        
        if ($time === false) {
        	//$time = Hapyfish_Island_Cache_Login::getLastLoginTime($uid);
        	$time = time();
        	$cache->add($key, $time);
        }
        
        return $time;
	}
	
	public static function getLastOutPlantPeopleTime($uid, $itemId, $islandId)
	{
        $key = 'i:tm:opplant:' . $uid . ':' . $itemId;
        $cache = Hapyfish2_Cache_Factory::getMC($uid);
        $time = $cache->get($key);
        
        if ($time === false) {
        	$time = self::getLastOutIslandPeopleTime($uid, $islandId);
        	$cache->add($key, $time);
        }
        
        return $time;
	}
	
	public static function canOutIslandPeople($uid, $islandId)
	{
		$key = 'i:lk:opisland:' . $uid . ':' . $islandId;
		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		return $cache->add($key, 1, 120);
	}
	
	public static function canOutPlantPeopleOfItem($uid, $itemId)
	{
		$key = 'i:lk:opplant:' . $uid . ':' . $itemId;
		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		return $cache->add($key, 1, 120);		
	}
	
	public static function updateLastOutIslandPeopleTime($uid, $time, $islandId)
	{
        $key = 'i:tm:opisland:' . $uid . ':' . $islandId;
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