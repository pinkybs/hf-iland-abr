<?php

class Hapyfish_Island_Cache_User
{
	public static function getExp($uid)
	{
		$key = 'Exp_' . $uid;
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$exp = $cache->get($key);
		if ($exp === false) {
			//load from db
			$db = Hapyfish_Island_Dal_User::getDefaultInstance();
			$exp = $db->getExp($uid);
			$cache->add($key, $exp, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
		}
		
		return $exp;
	}
	
	public static function cleanExp($uid)
	{
		$key = 'Exp_' . $uid;
		$cache = Hapyfish_Cache_Memcached::getInstance();
		return 	$cache->delete($key);
	}
	
	public static function getCoin($uid)
	{
		$db = Hapyfish_Island_Dal_User::getDefaultInstance();
		return $db->getCoin($uid);
	}
	
	public static function getGold($uid)
	{
		$db = Hapyfish_Island_Dal_User::getDefaultInstance();
		return $db->getGold($uid);
	}	
	
	public static function incExp($uid, $exp)
	{
		if (empty($exp)) {
			return ;
		}
		try {
			$db = Hapyfish_Island_Dal_User::getDefaultInstance();
			$db->incExp($uid, $exp);
			
			$key = 'Exp_' . $uid;
			$cache = Hapyfish_Cache_Memcached::getInstance();
			$exp = $cache->increment($key, $exp);
		} catch (Exception $e) {
			err_log($e->getMessage());
		}
	}
	
	public static function incCoin($uid, $coin)
	{
		if (empty($coin)) {
			return ;
		}
		$db = Hapyfish_Island_Dal_User::getDefaultInstance();
		$db->incCoin($uid, $coin);
	}
	
	public static function decCoin($uid, $coin)
	{
		if (empty($coin)) {
			return ;
		}
		$db = Hapyfish_Island_Dal_User::getDefaultInstance();
		$db->decCoin($uid, $coin);
	}	
	
	public static function incCoinAndExp($uid, $coin, $exp)
	{
		if (empty($coin)) {
			$coin = 0;
		}
		if (empty($exp)) {
			$exp = 0;
		}
		if ($coin == 0 && $exp == 0) {
			return ;
		}
		
		try {
			$db = Hapyfish_Island_Dal_User::getDefaultInstance();
			$db->incCoinAndExp($uid, $coin, $exp);
			
			if ($exp > 0) {
				$key = 'Exp_' . $uid;
				$cache = Hapyfish_Cache_Memcached::getInstance();
				$exp = $cache->increment($key, $exp);
			}
		} catch (Exception $e) {
			err_log($e->getMessage());
		}
	}
	
	public static function incGold($uid, $gold)
	{
		if (empty($gold)) {
			return ;
		}
		$db = Hapyfish_Island_Dal_User::getDefaultInstance();
		$db->incGold($uid, $gold);
	}
	
	public static function decGold($uid, $gold)
	{
		if (empty($gold)) {
			return ;
		}
		$db = Hapyfish_Island_Dal_User::getDefaultInstance();
		$db->decGold($uid, $gold);
	}
	
	//uid
	//next_level_exp
	//level
	//island_level
	//island_name
	public static function getLevelInfo($uid)
	{
		$key = 'LevelInfo_' . $uid;
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$info = $cache->get($key);
		if ($info === false) {
			//load from db
			$db = Hapyfish_Island_Dal_User::getDefaultInstance();
			$info = $db->getUserLevelInfo($uid);
			$cache->add($key, $info, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
		}
		
		return $info;		
	}
	
	public static function cleanLevelInfo($uid)
	{
		$key = 'LevelInfo_' . $uid;
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$cache->delete($key);
	}
	
	public static function getPraise($uid)
	{
		$key = 'Praise_' . $uid;
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$praise = $cache->get($key);
		if ($praise === false) {
			//load from db
			$db = Hapyfish_Island_Dal_User::getDefaultInstance();
			$praise = $db->getUserPraise($uid);
			$cache->add($key, $praise, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
		}
		
		return $praise;
	}
	
	public static function updatePraise($uid, $change)
	{
		try {
			$db = Hapyfish_Island_Dal_User::getDefaultInstance();
			$db->updateUserPraise($uid, $change);
			$key = 'Praise_' . $uid;
			$cache = Hapyfish_Cache_Memcached::getInstance();
			$cache->delete($key);		
		}catch (Exception $e) {
			err_log($e->getMessage());
		}
	}
	
	public static function cleanPraise($uid)
	{
		$key = 'Praise_' . $uid;
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$cache->delete($key);
	}
	
	public static function getTitle($uid)
	{
		$key = 'Title_' . $uid;
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$title = $cache->get($key);
		if ($title === false) {
			//load from db
			$db = Hapyfish_Island_Dal_User::getDefaultInstance();
			$title = $db->getUserTitle($uid);
			$cache->add($key, $title, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
		}
		
		return $title;		
	}
	
	public static function updateTitle($uid, $title)
	{
	    try {
			$db = Hapyfish_Island_Dal_User::getDefaultInstance();
			$db->updateUserTitle($uid, $title);
			$key = 'Title_' . $uid;
			$cache = Hapyfish_Cache_Memcached::getInstance();
			$cache->set($key, $title, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);		
		}catch (Exception $e) {
			
		}		
	}
	
	public static function getDefenseCardTime($uid)
	{
		$key = 'DefenseCardTime_' . $uid;
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$defenseCardTime = $cache->get($key);
		if ($defenseCardTime === false) {
			//load from db
			$db = Hapyfish_Island_Dal_User::getDefaultInstance();
			$defenseCardTime = $db->getUserDefenseCardTime($uid);
			if (!$defenseCardTime) {
				$defenseCardTime = 0;
			}
			$cache->add($key, $defenseCardTime, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
		}
		
		if (!$defenseCardTime) {
			$defenseCardTime = 0;
		}
		return $defenseCardTime;		
	}
	
	public static function updateDefenseCardTime($uid, $time)
	{
		try {
			$db = Hapyfish_Island_Dal_User::getDefaultInstance();
			$db->updateUserDefenseCardTime($uid, $time);
			$key = 'DefenseCardTime_' . $uid;
			$cache = Hapyfish_Cache_Memcached::getInstance();
			$cache->set($key, $time, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);	
		} catch(Exception $e) {
			
		}
	}
	
	public static function getInsuranceCardTime($uid)
	{
		$key = 'InsuranceCardTime_' . $uid;
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$insuranceCardTime = $cache->get($key);
		if ($insuranceCardTime === false) {
			//load from db
			$db = Hapyfish_Island_Dal_User::getDefaultInstance();
			$insuranceCardTime = $db->getUserInsuranceCardTime($uid);
			if (!$insuranceCardTime) {
				$insuranceCardTime = 0;
			}
			$cache->add($key, $insuranceCardTime, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
		}
		
		if (!$insuranceCardTime) {
			$insuranceCardTime = 0;
		}
		
		return $insuranceCardTime;
	}
	
	public static function updateInsuranceCardTime($uid, $time)
	{
		try {
			$db = Hapyfish_Island_Dal_User::getDefaultInstance();
			$db->updateUserInsuranceCardTime($uid, $time);
			$key = 'InsuranceCardTime_' . $uid;
			$cache = Hapyfish_Cache_Memcached::getInstance();
			$cache->set($key, $time, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);	
		} catch(Exception $e) {
			
		}
	}
	
    public static function getPositionCount($uid)
    {
		$key = 'PositionCount_' . $uid;
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$positionCount = $cache->get($key);
		if ($positionCount === false) {
			//load from database
			$db = Hapyfish_Island_Dal_User::getDefaultInstance();
			$positionCount = $db->getUserPositionCount($uid);
			if ($positionCount) {
				$cache->add($key, $positionCount, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
			}
		}
		
		return $positionCount;
    }
    
	public static function updatePositionCount($uid, $change = 1)
	{
		try {
			$db = Hapyfish_Island_Dal_User::getDefaultInstance();
			$db->updateUserPositionCount($uid, $change);
			$key = 'PositionCount_' . $uid;
			$cache = Hapyfish_Cache_Memcached::getInstance();
			$cache->delete($key);	
		} catch(Exception $e) {
			
		}
	}
	
	public static function cleanPositionCount($uid, $change = 1)
	{
		$key = 'PositionCount_' . $uid;
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$cache->delete($key);
	}	
	
    /**
     * get user level info
     *
     * @return array
     */
    public static function getUserLevelInfoByLevel($level)
    {
		$key = 'UserLevelInfoByLevel_' . $level;
		$cache = Hapyfish_Cache_Memcached::getInstance();
		$info = $cache->get($key);
		if ($info === false) {
			//load from database
			$db = Hapyfish_Island_Dal_User::getDefaultInstance();
			$info = $db->getUserLevelInfoByLevel($level);
			if ($info) {
				$cache->add($key, $info, Hapyfish_Cache_Memcached::LIFE_TIME_ONE_MONTH);
			}
		}
		
		return $info;
    	
    }

}