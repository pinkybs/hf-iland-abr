<?php

/** @see Bll_Cache */
require_once 'Bll/Cache.php';

/**
 * Visit Cache
 *
 * @package    Bll/Cache
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2010/01/20    Liz
*/
class Bll_Cache_Island_User
{
    private static $_prefix = 'Bll_Cache_Island_User';

    public static function getCacheKey($salt, $params = null)
    {
        return Bll_Cache::getCacheKey(self::$_prefix, $salt, $params);
    }

    /**
     * get user cache info
     *
     * @param int $uid
     */
    public static function getUserCacheInfo($uid)
    {
        $key = self::getCacheKey('getUserCacheInfo', $uid);
        
        $result = Bll_Cache::get($key);
        if ( $result === false ) {
            $dalUser = Dal_Island_User::getDefaultInstance();
            //get user level info
            $result = $dalUser->getUserLevelInfo($uid);

            if ( $result != null ) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
            }
        }
        return $result;
    }

    /**
     * get user using background
     *
     * @param int $uid
     */
    public static function getUsingBackground($uid)
    {
        $key = self::getCacheKey('getUsingBackground', $uid);
        
        $result = Bll_Cache::get($key);
        if ( $result === false ) {
            $dalIsland = Dal_Island_Island::getDefaultInstance();
            //get user using background
            $result = $dalIsland->getUsingBackground($uid);

            if ( $result != null ) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
            }
        }
        return $result;
    }

    /**
     * get user using building
     *
     * @param int $uid
     */
    public static function getUsingBuilding($uid)
    {
        $key = self::getCacheKey('getUsingBuilding', $uid);
        
        $result = Bll_Cache::get($key);
        if ( $result === false ) {
            $dalBuilding = Dal_Island_Building::getDefaultInstance();
            //get user using building
            $result = $dalBuilding->getUsingBuilding($uid);

            if ( $result != null ) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
            }
        }
        return $result;
    }

    /**
     * get user using plant
     *
     * @param int $uid
     */
    public static function getUsingPlant($uid)
    {
        $key = self::getCacheKey('getUsingPlant', $uid);
        
        $result = Bll_Cache::get($key);
        if ( $result === false ) {
            $dalPlant = Dal_Island_Plant::getDefaultInstance();
            //get user using plant
            $result = $dalPlant->getUsingPlant($uid);

            if ( $result != null ) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
            }
        }
        return $result;
    }

    /**
     * get user all plant
     *
     * @param int $uid
     */
    public static function getUserPlantListAll($uid)
    {
        $key = self::getCacheKey('getUserPlantListAll', $uid);
        
        $result = Bll_Cache::get($key);
        if ( $result === false ) {
            $dalPlant = Dal_Island_Plant::getDefaultInstance();
            //get user all plant
            $result = $dalPlant->getUserPlantListAll($uid);

            if ( $result != null ) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
            }
        }
        return $result;
    }

    /**
     * get user plant list
     *
     * @param int $uid
     */
    public static function getUserPlantList($uid)
    {
        $key = self::getCacheKey('getUserPlantList', $uid);
        
        $result = Bll_Cache::get($key);
        if ( $result === false ) {
            $dalPlant = Dal_Island_Plant::getDefaultInstance();
            //get user all plant
            $userPlantList = $dalPlant->getUserPlantList($uid);
            //get user plant list by item id
            $userPlantItemIdList = $dalPlant->getUserPlantListByItemId($uid);
            
            $result = array('userPlantList' => $userPlantList,
                            'userPlantItemIdList' => $userPlantItemIdList);

            if ( $result != null ) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
            }
        }
        return $result;
    }

    /**
     * get plant list
     *
     * @param int $uid
     */
    public static function getListIslandPlant($uid)
    {
        $key = self::getCacheKey('2getListIslandPlant', $uid);
        
        $result = Bll_Cache::get($key);
        if ( $result === false ) {
            $dalPlant = Dal_Island_Plant::getDefaultInstance();
            $dalUser = Dal_Island_User::getDefaultInstance();
            
            $userInfo = $dalUser->getUserDockInfo($uid);
            $bids = $dalPlant->getUserPlantBidByid($uid);
            $result = $dalPlant->getListIslandPlant($userInfo['level'], $bids);

            if ( $result != null ) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
            }
        }
        return $result;
    }

    /**
     * get user ship list
     *
     * @param int $uid
     */
    public static function getUserShipList($uid)
    {
        $key = self::getCacheKey('getUserShipList', $uid);
        
        $result = Bll_Cache::get($key);
        if ( $result === false ) {
            $dalDock = Dal_Island_Dock::getDefaultInstance();
            //get user all plant
            $result = $dalDock->getUserShipList($uid);

            if ( $result != null ) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
            }
        }
        return $result;
    }

    /**
     * get user last plant time
     *
     * @param int $uid
     */
    public static function getUserLastPlantTime($uid)
    {
        $key = self::getCacheKey('getUserLastPlantTime', $uid);
        
        $result = Bll_Cache::get($key);
        if ( $result === false ) {
            $dalUser = Dal_Island_User::getDefaultInstance();
            //get user last plant time
            $result = $dalUser->getUserOtherInfo($uid);
            $result = $result['last_login_time'];
            
            if ( $result != null ) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
            }
        }
        return $result;
    }

    /**
     * get user last plant time by item id
     *
     * @param int $uid
     */
    public static function getUserLastPlantTimeByItemId($uid, $itemId)
    {
    	$key = $itemId . $uid;
        $key = self::getCacheKey('getUserLastPlantTimeByItemId', $key);
        
        $result = Bll_Cache::get($key);
        if ( $result === false ) {

            $result = Bll_Cache_Island_User::getUserLastPlantTime($uid);
            
            if ( $result != null ) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
            }
        }
        return $result;
    }
    
    /**
     * get user friends all list
     *
     * @param int $uid
     */
    public static function getUserFriendsAll($uid, $fids)
    {
        $key = self::getCacheKey('getUserFriendsAll1', $uid);
        
        $result = Bll_Cache::get($key);
        if ( $result === false ) {

            $dalRank = Dal_Island_Rank::getDefaultInstance();
            $result = $dalRank->getUserFriendsAll($fids);
            
            if ( $result != null ) {
                Bll_Cache::set($key, $result, 300);
            }
        }
        return $result;
    }
    
    /**
     * get user help info
     *
     * @param int $uid
     */
    public static function getUserHelpInfo($uid)
    {
        $key = self::getCacheKey('getUserHelpInfo', $uid);
        
        $result = Bll_Cache::get($key);
        if ( $result === false ) {
            $dalUser = Dal_Island_User::getDefaultInstance();
            $result = $dalUser->getUserHelpInfo($uid);
            
            if ( $result != null ) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_WEEK);
            }
        }
        return $result;
    }
    
    /**
     * check today has view news
     *
     * @param int $uid
     */
    public static function todayHasView($uid)
    {
        $key = self::getCacheKey('todayHasView', $uid);
        
        $result = Bll_Cache::get($key);
        if ( $result === false ) {
	        $time = time();
	        $today = date('Y-m-d', $time);
	        $lifetime = strtotime($today) + 86400 - $time;
	        if ($lifetime < 10) {
	        	$lifetime = 10;
	        }
	        Bll_Cache::set($key, 1, $lifetime);
	        $todayHasView = false;
        }
        else {
            $todayHasView = true;
        }
        return $todayHasView;
    }
    
    /**
     * get has view news count
     *
     * @param int $uid
     */
    public static function hasViewCount($uid)
    {
        $key = self::getCacheKey('hasViewCount', $uid);
        
        $result = Bll_Cache::get($key);
        if ( $result === false ) {
        	$hasViewCount = 0;
        }
        else {
        	$hasViewCount = $result;
        }
        
        return $result;
    }

    /**
     * update has view news count
     *
     * @param int $uid
     */
    public static function updateViewCount($uid)
    {
        $key = self::getCacheKey('hasViewCount', $uid);
        
        $result = Bll_Cache::get($key);
        if ( $result === false ) {
        	$hasViewCount = 0;
        }
        else {
        	$hasViewCount = $result;
        }
        $value = $hasViewCount+1;
        Bll_Cache::set($key, $value, Bll_Cache::LIFE_TIME_MAX);
        
        return $hasViewCount;
    }
    
    /**
     * check user show view news
     *
     * @param int $uid
     */
    public static function showViewNews($uid)
    {
        $showViewNews = false;
        
        $hasViewCount = self::hasViewCount($uid);
        if ( $hasViewCount < 7 ) {
        	$todayHasView = self::todayHasView($uid);
        	if ( $todayHasView === false ) {
        		self::updateViewCount($uid);
        		$showViewNews = true;
        	}
        }
        
        return $showViewNews;
    }
    
    /**
     * update user last plant time
     *
     * @param int $uid
     */
    public static function updateUserLastPlantTime($uid, $result)
    {
    	$key = self::getCacheKey('getUserLastPlantTime', $uid);
    	
    	Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
    }

    /**
     * update user last plant time by item id
     *
     * @param int $uid
     */
    public static function updateUserLastPlantTimeByItemId($key, $result)
    {
        $key = self::getCacheKey('getUserLastPlantTimeByItemId', $key);
        
        Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
    }
    
    public static function clearCache($name, $param = null)
    {
        Bll_Cache::delete(self::getCacheKey($name, $param));
    }

}