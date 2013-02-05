<?php

class Hapyfish2_Island_Cache_Counter
{
    public static function getSendGiftCount($uid)
    {
		$today = date('Ymd');
    	$key = 'i:u:giftcntdly:' . $uid;
		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		$data= $cache->get($key);
		if ($data === false) {
			$data = array($today, 3);
			$cache->set($key, $data, 864000);
		} else {
			if ($data[0] < $today) {
				$data = array($today, 3);
			}
		}
		
		return array('today' => $data[0], 'count' => $data[1]);
    }
    
	public static function updateSendGiftCount($uid, $info)
	{
		$key = 'i:u:giftcntdly:' . $uid;
		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		$data = array($info['today'], $info['count']);
		//10 days
		$cache->set($key, $data, 864000);
	}
	
	public static function getPlunderCount($uid)
	{
		$today = date('Ymd');
    	$key = 'i:u:pludercntdly:' . $uid;
		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		$data= $cache->get($key);
		if ($data === false) {
			$data = array($today, 3);
			$cache->set($key, $data, 864000);
		} else {
			if ($data[0] < $today) {
				$data = array($today, 3);
			}
		}
		
		return array('today' => $data[0], 'count' => $data[1]);
	}
	
	public static function updatePlunderCount($uid, $info)
	{
		$key = 'i:u:pludercntdly:' . $uid;
		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		$data = array($info['today'], $info['count']);
		//10 days
		$cache->set($key, $data, 864000);
	}
	
	public static function getDamageCount($uid)
	{
		$today = date('Ymd');
    	$key = 'i:u:damagecntdly:' . $uid;
		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		$data= $cache->get($key);
		if ($data === false) {
			$data = array($today, 10);
			$cache->set($key, $data, 864000);
		} else {
			if ($data[0] < $today) {
				$data = array($today, 10);
			}
		}
		
		return array('today' => $data[0], 'count' => $data[1]);
	}
	
	public static function updateDamageCount($uid, $info)
	{
		$key = 'i:u:damagecntdly:' . $uid;
		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		$data = array($info['today'], $info['count']);
		//10 days
		$cache->set($key, $data, 864000);
	}

    /**
     * get user mammon card remain time
     *
     * @return array
     */
    public static function getUserMammonRemainTime($uid)
    {
    	
		$today = date('Ymd');
    	$key = 'i:u:damagecntdly:' . $uid;
		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		$data= $cache->get($key);
		if ($data === false) {
			$data = array($today, 10);
			$cache->set($key, $data, 864000);
		} else {
			if ($data[0] < $today) {
				$data = array($today, 10);
			}
		}
		return array('today' => $data[0], 'count' => $data[1]);
		
		$key = 'i:u:mammonStart:' . $uid;
		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		$startTime= $cache->get($key);
		if ($startTime === false) {
			$startTime = 0;
			$cache->set($key, $startTime, 864000);
		}
		
		$key1 = 'i:u:mammonUsingTime:' . $uid;
		$usingTime = $cache->get($key1);
		if ($usingTime === false) {
			$usingTime = 0;
			$cache->set($key1, $usingTime, 864000);
		}
		
		$remainTime = $usingTime - (time() - $startTime);
		$remainTime = $remainTime < 0 ? 0 : $remainTime;
		
		return $remainTime;
    }
    
    public static function updateUserMammonTime($uid, $usingTime)
    {
		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		
		$key = 'i:u:mammonStart:' . $uid;
		$cache->set($key, time(), 864000);
		
		$typeKey = 'i:u:mammonUsingTime:' . $uid;
		$cache->set($typeKey, $usingTime, 864000);
    }

    /**
     * get user poor card remain time
     *
     * @return array
     */
    public static function getUserPoorRemainTime($uid)
    {
		$key = 'i:u:poorStartTime:' . $uid;
		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		$startTime = $cache->get($key);
		if ($startTime === false) {
			$startTime = 0;
			$cache->set($key, $startTime, 864000);
		}
		
		$key1 = 'i:u:poorUsingTime:' . $uid;
		$usingTime = $cache->get($key1);
		if ($usingTime === false) {
			$usingTime = 0;
			$cache->set($key1, $usingTime, 864000);
		}
		
		$remainTime = $usingTime - (time() - $startTime);
		$remainTime = $remainTime < 0 ? 0 : $remainTime;
		
		return $remainTime;
    }
    
    public static function updateUserPoorTime($uid, $usingTime)
    {
		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		
		$key = 'i:u:poorStartTime:' . $uid;
		$cache->set($key, time(), 864000);
		
		$typeKey = 'i:u:poorUsingTime:' . $uid;
		$cache->set($typeKey, $usingTime, 864000);
    }
    
    
    
 
    // 延迟更新补偿
    public static function getBuchangTF($uid)
    {
    	$key = 'i:u:buchangetf:' . $uid;
    	
    	$cache = Hapyfish2_Cache_Factory::getMC($uid);
		$val = $cache->get($key);
		
		return ($val ? true : false);
    }
    // 设置延迟更新补偿
	public static function setBuchangTF($uid)
    {
    	$key = 'i:u:buchangetf:' . $uid;
    	
    	$cache = Hapyfish2_Cache_Factory::getMC($uid);
		
        $remaintime = 86400 * 10;
        
        return $cache->add($key, 1, $remaintime);
    }
    // 清除设置延迟更新补偿
	public static function clearBuchangTF($uid)
    {
    	$key = 'i:u:buchangetf:' . $uid;
    	
    	$cache = Hapyfish2_Cache_Factory::getMC($uid);
		
        return $cache->delete($key);
    }
    
    
	// 获得玩家今天是否已经领取宝箱
    public static function getBottleTodayTF($uid)
    {
    	$key = 'bottle:todaytf:' . $uid;
    	
    	$cache = Hapyfish2_Cache_Factory::getMC($uid);
		$val = $cache->get($key);
		
		$result = $val > 0 ? true : false;
		return $result; 
		//return ($val ? true : false);
    }
    
    // 设置玩家今天已经获得过宝箱
    public static function updateBottleTodayTF($uid)
    {
    	$key = 'bottle:todaytf:' . $uid;
		
		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		$val = $cache->get($key);
		
		$num = --$val;
		$count = $num > 0 ? $num : 0;
		
		$cache->set($key, $count);
		
//		list($g, $i, $s) = explode(':', date('G:i:s'));
//        $remaintime = 86400 - ((int)$g * 3600 + (int)$i * 60 + (int)$s);
        
//        return $cache->add($key, 1, $remaintime);
    }
    

}