<?php

class Hapyfish2_Island_Cache_Mooch
{
    public static function getMoochPlantList($uid, $ids)
    {
    	$keys = array();
    	foreach ($ids as $id) {
    		$keys[] = 'i:u:mooch:plt:' . $uid . ':' . $id;
    	}
    	$cache = Hapyfish2_Cache_Factory::getMC($uid);
    	$data = $cache->getMulti($keys);
    	
    	$list = array();
    	foreach ($ids as $id) {
    		$key = 'i:u:mooch:plt:' . $uid . ':' . $id;
    		$list[$id] = $data[$key];
    	}
    	
    	return $list;
    }
	
	public static function getMoochPlant($uid, $plantId)
    {
        $key = 'i:u:mooch:plt:' . $uid . ':' . $plantId;
        $cache = Hapyfish2_Cache_Factory::getMC($uid);
		$data = $cache->get($key);
		if ($data === false) {
			return array();
		}
		
		return $data;
    }
    
    public static function moochPlant($uid, $plantId, $data)
    {
        $key = 'i:u:mooch:plt:' . $uid . ':' . $plantId;
        $cache = Hapyfish2_Cache_Factory::getMC($uid);
		$cache->set($key, $data);
    }
    
    public static function clearMoochPlant($uid, $plantId)
    {
        $key = 'i:u:mooch:plt:' . $uid . ':' . $plantId;
        $cache = Hapyfish2_Cache_Factory::getMC($uid);
        $cache->delete($key);
    }
    
    public static function getMoochShipList($uid, $ids)
    {
    	$keys = array();
    	foreach ($ids as $id) {
    		$keys[] = 'i:u:mooch:ship:' . $uid . ':' . $id;
    	}
    	$cache = Hapyfish2_Cache_Factory::getMC($uid);
    	$data = $cache->getMulti($keys);
    	
    	$list = array();
    	foreach ($ids as $id) {
    		$key = 'i:u:mooch:ship:' . $uid . ':' . $id;
    		$list[$id] = $data[$key];
    	}
    	
    	return $list;
    }
    
    public static function getMoochShip($uid, $postionId)
    {
        $key = 'i:u:mooch:ship:' . $uid . ':' . $postionId;
        $cache = Hapyfish2_Cache_Factory::getMC($uid);
		$data = $cache->get($key);
		if ($data === false) {
			return array();
		}
		
		return $data;
    }
    
    public static function moochShip($uid, $postionId, $data)
    {
        $key = 'i:u:mooch:ship:' . $uid . ':' . $postionId;
        $cache = Hapyfish2_Cache_Factory::getMC($uid);
		$cache->set($key, $data);
    }
    
    public static function clearMoochShip($uid, $postionId)
    {
        $key = 'i:u:mooch:ship:' . $uid . ':' . $postionId;
        $cache = Hapyfish2_Cache_Factory::getMC($uid);
        $cache->delete($key);
    }
    

}