<?php

class Hapyfish2_Island_Bll_Strom
{
	/**
     * get user strom status
     *
     * @param integer $uid
     * @return array
	 */
	public static function getStrom($uid)
	{
		$ids = array(0, 1, 2);
		
		foreach ($ids as $id) {
			$key = 'i:u:flashstrom' . $uid . $id;
			$cache = Hapyfish2_Cache_Factory::getMC($uid);
			$result['strom_' . $id] = $cache->get($key);
			
			if(!$result['strom_' . $id]) {
				$result['strom_' . $id] = -1;
			}
		}

		return $result;
		
	}
	
	/**
     * add user coin
     *
     * @param integer $uid
     * @return array
	 */
	public static function addCoin($uid)
	{	
		$ids = array(0, 1, 2);
		
		foreach ($ids as $id) {
			$key = 'i:u:flashstrom' . $uid . $id;
			$cache = Hapyfish2_Cache_Factory::getMC($uid);
			$state[$id] = $cache->get($key);
		}
		$now = time();
		
		$timeKey = 'i:u:flashtime' . $uid;
		$timeCache = Hapyfish2_Cache_Factory::getMC($uid);
		$lastTime = $timeCache->get($timeKey);
		
		$coin = 1000;
		$status = 1;
		if($state[0] == -1 || !$state[0]) {
			Hapyfish2_Island_HFC_User::incUserCoin($uid, $coin);
			
			$key = 'i:u:flashstrom' . $uid . 0;
			$cache = Hapyfish2_Cache_Factory::getMC($uid);
			$cache->set($key, 1);
			
			self::setStartTime($uid, $now);
			
			$result = array('coinChange' => $coin);
			$resultVo = array('status' => $status, 'result' => $result);
			return $resultVo;
		}
		
		if($now - $lastTime > 3600) {
			if($state[1] == -1) {
				Hapyfish2_Island_HFC_User::incUserCoin($uid, $coin);
				
				$key = 'i:u:flashstrom' . $uid . 1;
				$cache = Hapyfish2_Cache_Factory::getMC($uid);
				$cache->set($key, 1);
				
				$result = array('coinChange' => $coin);
				$resultVo = array('status' => $status, 'result' => $result);
				return $resultVo;
			}
		}

		if($now - $lastTime > 3600 * 3) {
			if($state[2] == -1) {
				Hapyfish2_Island_HFC_User::incUserCoin($uid, $coin);
				
				$key = 'i:u:flashstrom' . $uid . 2;
				$cache = Hapyfish2_Cache_Factory::getMC($uid);
				$cache->set($key, 1);
				
				$result = array('coinChange' => $coin);
				$resultVo = array('status' => $status, 'result' => $result);
				return $resultVo;
			}
		}
		$status = array('status' => -1, 'content' => 'serverWord_101');
		
		return $status;
	}
	
	/**
	 * set start time
	 */
	public static function setStartTime($uid, $start_time)
	{
		$key = 'i:u:flashtime' . $uid;
		$cache = Hapyfish2_Cache_Factory::getMC($uid);
		$cache->set($key, $start_time);
	}
	
}