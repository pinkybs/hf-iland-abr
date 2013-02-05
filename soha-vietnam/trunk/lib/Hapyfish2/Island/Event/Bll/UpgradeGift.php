<?php
class Hapyfish2_Island_Event_Bll_UpgradeGift
{
	
	public static function getTF($uid)
	{
		$key = 'i:u:upgradegift:' . $uid;
		
		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		$data = $cache->get($key);
		return ($data ? true : false);
	}
	
	public static function setTF($uid)
	{
		$key = 'i:u:upgradegift:' . $uid;
		
		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		return $cache->add($key, 1, Hapyfish2_Cache_Memcached::LIFE_TIME_ONE_MONTH);
	}
	
	public static function clearTF($uid) 
	{
		$key = 'i:u:upgradegift:' . $uid;
		
		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		return $cache->delete($key);		
	}
	
	public static function gifttouser($uid)
	{
		if ($uid) {
			
			$gold = 10;
			$coin = 10000;
			/*
			3星建设卡x3张（56741）、
			双倍经验卡x5张（78441）
			船只加速卡3x5张（26441）、
			请神卡x5张（67241）、
			战神像x1（85131）
			*/		
			
			$items[] = array('56741', 3);
			$items[] = array('74841', 5);
			$items[] = array('26441', 5);
			$items[] = array('67241', 5);
			$items[] = array('81531', 1);
			
			$com = new Hapyfish2_Island_Bll_Compensation();
			$com->setCoin($coin);
			$com->setGold($gold);
			foreach ($items as $key => $val) {
				$com->setItem($val[0], $val[1]);
			}
			
			return $com->sendOne($uid,'');
		} else {
			return false;
		}
	}
	
	
}