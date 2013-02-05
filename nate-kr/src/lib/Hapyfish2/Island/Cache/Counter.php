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
}