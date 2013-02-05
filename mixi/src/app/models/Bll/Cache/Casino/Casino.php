<?php

/** @see Bll_Cache */
require_once 'Bll/Cache.php';

/**
 * Visit Cache
 *
 * @package    Bll/Cache
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2010/08/23    Liz
*/
class Bll_Cache_Casino_Casino
{
    private static $_prefix = 'Bll_Cache_Casino_Casino';

    public static function getCacheKey($salt, $params = null)
    {
        return Bll_Cache::getCacheKey(self::$_prefix, $salt, $params);
    }

    /**
     * get casino shop info
     *
     */
    public static function getCasinoShopList($time)
    {
        $key = self::getCacheKey('newgetCasinoShopList', $time);
        
        $result = Bll_Cache::get($key);
        if ( $result === false ) {
        	$dalCasinoCasino = Dal_Casino_Casino::getDefaultInstance();
			$result = $dalCasinoCasino->getTodayShopList($time);
			
            if ( $result != null ) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
            }
        }
        return $result;
    }

    /**
     * get award list
     *
     */
    public static function getAwardList()
    {
        $key = self::getCacheKey('newgetAwardList');
        
        $result = Bll_Cache::get($key);
        if ( $result === false ) {
        	$dalCasinoCasino = Dal_Casino_Casino::getDefaultInstance();
			$result = $dalCasinoCasino->getAwardList();
			
			for ( $i=0,$iCount=count($result); $i<$iCount; $i++ ) {
				if ( $result[$i]['item_cid'] > 0 ) {
					$result[$i]['itemCid'] = $result[$i]['item_cid'];
				}
				unset($result[$i]['item_cid']);
			}
			
            if ( $result != null ) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_MONTH);
            }
        }
        return $result;
    }
    
    /**
     * get award list
     *
     */
    public static function getAwardRandArray()
    {
        $key = self::getCacheKey('newgetAwardRandArray');
        
        $result = Bll_Cache::get($key);
        if ( $result === false ) {
        	$dalCasinoCasino = Dal_Casino_Casino::getDefaultInstance();
			$awardList = $dalCasinoCasino->getAwardOddsList();
			
			$randArray = array();
			for ( $i=0,$iCount=count($awardList); $i<$iCount; $i++ ) {
				for ( $j=0; $j<$awardList[$i]['odds']; $j++ ) {
					$randArray[] = $awardList[$i]['id'];
				}
			}
			
			shuffle($randArray);
			$result = $randArray;
            if ( $result != null ) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_MONTH);
            }
        }
        return $result;
    }
    
    /**
     * get award id list
     *
     */
    public static function getAwardIdList()
    {
        $key = self::getCacheKey('newgetAwardIdList');
        
        $result = Bll_Cache::get($key);
        if ( $result === false ) {
        	$dalCasinoCasino = Dal_Casino_Casino::getDefaultInstance();
			$result = $dalCasinoCasino->getAwardIdList();
			
            if ( $result != null ) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_MONTH);
            }
        }
        return $result;
    }
    
    /**
     * get coupon list
     *
     */
    public static function getUserCouponList($uid)
    {
        $key = self::getCacheKey('newgetUserCouponList', $uid);
        
        $result = Bll_Cache::get($key);
        if ( $result === false ) {
        	$dalCasinoCasino = Dal_Casino_Casino::getDefaultInstance();
			$result = $dalCasinoCasino->getUserCouponList($uid);
			
            if ( $result != null ) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
            }
        }
        return $result;
    }

    /**
     * get user casino count
     *
     */
    public static function getUserCasinoCount($uid)
    {
        $key = self::getCacheKey('newgetUserCasinoCount', $uid);
        
        $result = Bll_Cache::get($key);
        if ( $result === false ) {
        	$dalCasinoCasino = Dal_Casino_Casino::getDefaultInstance();
			$result = $dalCasinoCasino->getUserCasinoCount($uid);
			
            if ( $result != null ) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_MONTH);
            }
        }
        return $result;
    }

    /**
     * get prizes info
     *
     */
    public static function getPrizeInfo($prizesId)
    {
        $key = self::getCacheKey('newgetPrizeInfo', $prizesId);
        
        $result = Bll_Cache::get($key);
        if ( $result === false ) {
        	$dalCasinoCasino = Dal_Casino_Casino::getDefaultInstance();
			$result = $dalCasinoCasino->getPrizeInfo($prizesId);
			
            if ( $result != null ) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
            }
        }
        return $result;
    }

    /**
     * get award info
     *
     */
    public static function getAwardInfo($award)
    {
        $key = self::getCacheKey('newgetAwardInfo', $award);
        
        $result = Bll_Cache::get($key);
        if ( $result === false ) {
        	$dalCasinoCasino = Dal_Casino_Casino::getDefaultInstance();
			$result = $dalCasinoCasino->getAwardInfo($award);
			
            if ( $result != null ) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_MONTH);
            }
        }
        return $result;
    }
    
    /**
     * get prizes raffle count
     *
     */
    public static function getPrizesRaffleCount($prizesId)
    {
        $key = self::getCacheKey('newprizesRaffleCount', $prizesId);
        
        $result = Bll_Cache::get($key);
        if ( $result === false ) {
        	$result = 0;
			
            if ( $result != null ) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
            }
        }
        return $result;
    }

    /**
     * has get by bid
     *
     */
    public static function hasGetByBid($uid, $bid)
    {
        $key = self::getCacheKey('newhasGetByBid', array($uid, $bid));
        
        $result = Bll_Cache::get($key);
        if ( $result === false ) {
        	$dalCasinoCasino = Dal_Casino_Casino::getDefaultInstance();
			$result = $dalCasinoCasino->hasGetByBid($uid, $bid);
			
            if ( $result != null ) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
            }
        }
        return $result;
    }
    
    /**
     * update prizes raffle count
     *
     */
    public static function updatePrizesRaffleCount($prizesId, $count)
    {
        $key = self::getCacheKey('newprizesRaffleCount', $prizesId);
        
        $result = Bll_Cache::get($key);
        if ( $result === false ) {
        	$result = $count;
        }
        else {
        	$result = $result + $count;
        }
        
        Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
        
        return $result;
    }
    
    /**
     * get user lv point
     *
     */
    public static function getUserLvPoint($uid)
    {
        $key = self::getCacheKey('newgetUserLvPoint', $uid);
        
        $result = Bll_Cache::get($key);
        if ( $result === false ) {
        	$dalCasinoCasino = Dal_Casino_Casino::getDefaultInstance();
			$result = $dalCasinoCasino->getUserLvPoint($uid);
        	
			if ( empty($result) ) {
				$result = 0;
			}
            if ( $result != null ) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_MONTH);
            }
        }
        return $result;
    }

    /**
     * get user lv point rank num
     *
     */
    public static function getUserLvPointRank($uid, $point)
    {
        $key = self::getCacheKey('newgetUserLvPointRank', $uid);
        
        $result = Bll_Cache::get($key);
        if ( $result === false ) {        	
        	$dalCasinoCasino = Dal_Casino_Casino::getDefaultInstance();
			$result = $dalCasinoCasino->getUserLvPointRank($point);
			
            if ( $result != null ) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
            }
        }
        return $result;
    }
    
    /**
     * get first lv point
     *
     */
    public static function getFirstLvPoint()
    {
        $key = self::getCacheKey('newgetFirstLvPoint');
        
        $result = Bll_Cache::get($key);
        if ( $result === false ) {
        	$dalCasinoCasino = Dal_Casino_Casino::getDefaultInstance();
			$result = $dalCasinoCasino->getFirstLvPoint();
			if ( empty($result) ) {
				$result = 0;
			}
            if ( $result != null ) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_MONTH);
            }
        }
        return $result;
    }

    /**
     * get avg lv point
     *
     */
    public static function getAvgLvPoint()
    {
        $key = self::getCacheKey('newgetAvgLvPoint');
        
    	$time = time();
        $today = date('Y-m-d', $time);
        $lifetime = strtotime($today) + 86400 - $time;
        if ($lifetime < 10) {
        	$lifetime = 10;
        }
        
        $avgCount = Bll_Cache::get($key);
        if ( $avgCount === false ) {
        	$dalCasinoCasino = Dal_Casino_Casino::getDefaultInstance();
			$AllLvPoint = $dalCasinoCasino->getAllLvPoint();
			$AllJoinLvUserCount = $dalCasinoCasino->getAllJoinLvUserCount();
			
			$avgCount = round($AllLvPoint / $AllJoinLvUserCount);
			
			if ( empty($avgCount) ) {
				$avgCount = 0;
			}
            if ( $avgCount != null ) {
                Bll_Cache::set($key, $avgCount, $lifetime);
            }
        }
        return $avgCount;
    }
    
    /**
     * check user has join lv
     *
     * @param int $uid
     */
    public static function isJoinLv($uid)
    {
        $key = self::getCacheKey('newisJoinLv', $uid);
        
        $result = Bll_Cache::get($key);
        if ( $result === false ) {
            $dalPlant = Dal_Casino_Casino::getDefaultInstance();
            $result = $dalPlant->isJoinLv($uid);
            
            if ( $result != null ) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_MONTH);
            }
        }
        return $result;
    }
    
    /**
     * check user has change
     *
     * @param int $uid
     */
    public static function hasChange($uid)
    {
        $key = self::getCacheKey('newhasChange', $uid);
        
        $result = Bll_Cache::get($key);
        if ( $result === false ) {
            $dalPlant = Dal_Casino_Casino::getDefaultInstance();
            $userPoint = $dalPlant->getUserLvPoint($uid);
            $result = $userPoint > 0 ? false : true;
            
            if ( $result != null ) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_MONTH);
            }
        }
        return $result;
    }
    
    /**
     * get user today lv count
     *
     */
    public static function getUserTodayLvCount($uid)
    {
        $key = self::getCacheKey('newgetUserTodayLvCount', $uid);
        
    	$time = time();
        $today = date('Y-m-d', $time);
        $lifetime = strtotime($today) + 86400 - $time;
        if ($lifetime < 10) {
        	$lifetime = 10;
        }
        
        $result = Bll_Cache::get($key);
        if ( $result === false ) {
        	$result = 0;
            if ( $result != null ) {
                Bll_Cache::set($key, $result, $lifetime);
            }
        }
        return $result;
    }
    
    /**
     * update user today lv count
     *
     */
    public static function updateUserTodayLvCount($uid, $change)
    {
        $key = self::getCacheKey('newgetUserTodayLvCount', $uid);
        
    	$time = time();
        $today = date('Y-m-d', $time);
        $lifetime = strtotime($today) + 86400 - $time;
        if ($lifetime < 10) {
        	$lifetime = 10;
        }
        
        $result = Bll_Cache::get($key);
        if ( $result === false ) {
			$result = 1;
        }
        else {
        	$result = $result + $change;
        }
    
        if ( $result != null ) {
            Bll_Cache::set($key, $result, $lifetime);
        }
        return $result;
    }
    
    
    public static function clearCache($name, $param = null)
    {
        Bll_Cache::delete(self::getCacheKey($name, $param));
    }

}