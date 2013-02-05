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
class Bll_Cache_Island_Dock
{
    private static $_prefix = 'Bll_Cache_Island_Dock';

    public static function getCacheKey($salt, $params = null)
    {
        return Bll_Cache::getCacheKey(self::$_prefix, $salt, $params);
    }

    /**
     * get user unlock ship list
     *
     * @param int $uid
     * @param int $fid
     */
    public static function getUserUnlockShipList($uid, $pid)
    {
        $key = self::getCacheKey('getUserUnlockShipList', array($uid, $pid));
        
        $result = Bll_Cache::get($key);
        if ( $result === false ) {
            $dalDock = Dal_Island_Dock::getDefaultInstance();
            //get user ship list
            $result = $dalDock->getUserUnlockShipList($uid, $pid);

            if ( $result != null ) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
            }
        }
        return $result;
    }

    /**
     * get user position count
     *
     * @param int $uid
     */
    public static function getUserPositionCount($uid)
    {
        $key = self::getCacheKey('getUserPositionCount', $uid);
        
        $result = Bll_Cache::get($key);
        if ( $result === false ) {
            $dalUser = Dal_Island_User::getDefaultInstance();
            //get user position count
            $result = $dalUser->getUserPositionCount($uid);

            if ( $result != null ) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
            }
        }
        return $result;
    }

    /**
     * get user position list
     *
     * @param int $uid
     */
    public static function getUserPositionList($uid)
    {
        $key = self::getCacheKey('getUserPositionList', $uid);
        
        $result = Bll_Cache::get($key);
        if ( $result === false ) {
        	
	        $dalDock = Dal_Island_Dock::getDefaultInstance();
	        //get user position list
	        $result = $dalDock->getUserPositionList($uid);

            if ( $result != null ) {
                Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
            }
        }
        return $result;
    }
    
    public static function clearUnlockShipList($uid, $pid)
    {
    	$key = self::getCacheKey('getUserUnlockShipList', array($uid, $pid));
    	Bll_Cache::delete($key);
    }

    public static function clearCache($name, $param = null)
    {
        Bll_Cache::delete(self::getCacheKey($name, $param));
    }
}