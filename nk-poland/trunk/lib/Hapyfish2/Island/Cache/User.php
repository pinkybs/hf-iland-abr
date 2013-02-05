<?php

class Hapyfish2_Island_Cache_User
{
	public static function isAppUser($uid)
    {
        $key = 'i:u:isapp:' . $uid;
        $cache = Hapyfish2_Cache_Factory::getMC($uid);
        $data = $cache->get($key);
        if ($data === false) {
        	if ($cache->isNotFound()) {
				$levelInfo = Hapyfish2_Island_HFC_User::getUserLevel($uid);
				if (!$levelInfo) {
					return false;
				} else {
					$data = 'Y';
					$cache->set($key, $data);
					return true;
				}
			} else {
				return false;
			}
        }
        if ($data == 'Y') {
        	return true;
        } else {
        	return false;
        }
    }
    
    public static function setAppUser($uid)
    {
        $key = 'i:u:isapp:' . $uid;
        $cache = Hapyfish2_Cache_Factory::getMC($uid);
        $data = $cache->get($key);
        $cache->set($key, 'Y');
    }
    
    public static function canEZineShow($uid, $todayTime, $version)
    {
        $key = 'i:u:ezinecount:' . $uid;
        $cache = Hapyfish2_Cache_Factory::getMC($uid);
        $data = $cache->get($key);
        $show = true;
        if ($data === false) {
        	$data = array($version, $todayTime, 0);
        	$cache->add($key, $data, 864000);
        } else {
        	if ($data[0] < $version) {
        		$data = array($version, $todayTime, 0);
        		$cache->set($key, $data, 864000);
        	} else if ($data[1] < $todayTime) {
				if ($data[2] >= 7) {
        			$show = false;
        		} else {
	        		$data[1] = $todayTime;
	        		$data[2] += 1;
	        		$cache->set($key, $data, 864000);
        		}
        	} else {
        		$show = false;
        	}
        }
        
        return $show;
    }
    
    public static function showEZine($uid, $todayTime)
    {
    	$EZineStatus = Hapyfish2_Island_Cache_BasicInfo::getEZineStatus();
    	
    	if ($EZineStatus['show']) {
    		return self::canEZineShow($uid, $todayTime, $EZineStatus['ver']);
    	} else {
    		return false;
    	}
    }

	public static function hasGetVipBuilding($uid)
    {
        $key = 'i:u:getvipbuild:' . $uid;
        $cache = Hapyfish2_Cache_Factory::getMC($uid);
        $data = $cache->get($key);
        if ($data == 'Y') {
        	return true;
        } else {
        	return false;
        }
    }
    
    public static function setVipBuilding($uid, $data = 'Y')
    {
        $key = 'i:u:getvipbuild:' . $uid;
        $cache = Hapyfish2_Cache_Factory::getMC($uid);
        $cache->set($key, $data);
    }
    
	public static function getVipGift($uid)
    {
        $key = 'i:u:getvipgift:' . $uid;
        $cache = Hapyfish2_Cache_Factory::getMC($uid);
        $data = $cache->get($key);
        if ($data == 'Y') {
        	return;
        } else {
        	$newPlant = array(
				'uid' => $uid,
				'cid' => 632,
				'item_id' => 6,
				'x' => 0,
				'y' => 0,
				'z' => 0,
				'mirro' => 0,
				'can_find' => 0,
				'level' => 1,
				'status' => 0,
				'buy_time' => time(),
				'item_type' => 32
			);
			Hapyfish2_Island_HFC_Plant::addOne($uid, $newPlant);
			Hapyfish2_Island_HFC_Card::addUserCard($uid, 26441, 3);
			Hapyfish2_Island_HFC_User::incUserCoin($uid, 5000);
        	
        	$cache->set($key, 'Y');
        }
    }
    
	public static function checkVipGift($uid)
    {
        $key = 'i:u:getvipgift:' . $uid;
        $cache = Hapyfish2_Cache_Factory::getMC($uid);
        $data = $cache->get($key);
        if ($data == 'Y') {
        	return true;
        } else {
        	return false;
        }
    }

	public static function setVipGift($uid, $data)
    {
        $key = 'i:u:getvipgift:' . $uid;
        $cache = Hapyfish2_Cache_Factory::getMC($uid);
        $cache->set($key, $data);
    }
        
	public static function getIslandTip($uid)
    {
        $key = 'i:u:isltp:' . $uid;
        $cache = Hapyfish2_Cache_Factory::getMC($uid);
        $data = $cache->get($key);
        if ($data === false) {
			$data = '1';
        	$cache->add($key, $data);
        }
        return $data;
    }
    
    public static function setIslandTip($uid, $mapIconState)
    {
        $key = 'i:u:isltp:' . $uid;
        $cache = Hapyfish2_Cache_Factory::getMC($uid);
        $cache->set($key, $mapIconState);
    }

	public static function isFirstIntoIsland($uid, $islandId)
    {
        $key = 'i:u:isfstin:' . $uid . ':' . $islandId;
        $cache = Hapyfish2_Cache_Factory::getMC($uid);
        $data = $cache->get($key);
        if ($data === false) {
        	$cache->add($key, 'Y');
        	return true;
        }
        
        return false;
    }
    
}