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
class Bll_Cache_Island_Visit
{
    private static $_prefix = 'Bll_Cache_Island_Visit';

    public static function getCacheKey($salt, $params = null)
    {
        return Bll_Cache::getCacheKey(self::$_prefix, $salt, $params);
    }

    /**
     * check is visit 
     *
     * @param int $uid
     * @param int $fid
     * @return bool
     */
    public static function isVisit($uid, $fid)
    {
        $key = self::getCacheKey('isVisit', array($uid, $fid));
        $result = Bll_Cache::get($key);
        $isVisit = false;
        if ($result) {
        	$isVisit = true;
        }

        return $isVisit;
    }

    /**
     * set visit info
     *
     * @param int $uid
     * @param int $fid
     */
    public static function setVisit($uid, $fid)
    {
    	$key = self::getCacheKey('isVisit', array($uid, $fid));
        $time = time();
        $today = date('Y-m-d', $time);
        $lifetime = strtotime($today) + 86400 - $time;
        if ($lifetime < 10) {
        	$lifetime = 10;
        }
        
        $dalVisit = Dal_Mongo_Visit::getDefaultInstance();
        $isVisitMongo = $dalVisit->getUserVisitInfo($uid, $fid);
        if ( !$isVisitMongo ) {
            $dalVisit->insertUserVisit(array('uid' => $uid,'fid' => $fid));
            
            $dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
            //update user achievement,num_6
            $dalMongoAchievement->updateUserAchievementByField($uid, 'num_6', 1);
        }
        
        Bll_Cache::set($key, 1, $lifetime);
    }

    /**
     * check is visit 
     *
     * @param int $uid
     * @param int $fid
     * @return bool
     */
    public static function isTodayVisit($uid, $fid)
    {
        $key = self::getCacheKey('isTodayVisit', array($uid, $fid));
        $result = Bll_Cache::get($key);
        $isVisit = false;
        if ($result) {
            $isVisit = true;
        }

        return $isVisit;
    }

    /**
     * set visit info
     *
     * @param int $uid
     * @param int $fid
     */
    public static function setTodayVisit($uid, $fid)
    {
        $key = self::getCacheKey('isTodayVisit', array($uid, $fid));
        $time = time();
        $today = date('Y-m-d', $time);
        $lifetime = strtotime($today) + 86400 - $time;
        if ($lifetime < 10) {
            $lifetime = 10;
        }
        $todayDate = date('Ymd');
        
        $dalVisit = Dal_Mongo_Visit::getDefaultInstance();
        
        $isTodayVisitMongo = $dalVisit->getUserTodayVisitInfo($uid, $fid, $todayDate);
        if (!$isTodayVisitMongo) {
            $dalVisit->insertUserTodayVisit(array('uid' => $uid,'fid' => $fid), date('Ymd'));
            
            $dalMongoAchievement = Dal_Mongo_Achievement::getDefaultInstance();
            //update user achievement,num_6
            $dalMongoAchievement->updateUserTodayAchievementByField($uid, 'num_6', 1);
            //update user achievement,num_6
            $dalMongoAchievement->updateUserAchievementByField($uid, 'num_6', 1);
        }

        Bll_Cache::set($key, 1, $lifetime);
    }
    
    public static function deleteVisit($uid, $fid)
    {
    	$key = self::getCacheKey('isVisit', array($uid, $fid));
    	Bll_Cache::delete($key);
    }

    public static function deleteTodayVisit($uid, $fid)
    {
        $key = self::getCacheKey('isTodayVisit', array($uid, $fid));
        Bll_Cache::delete($key);
    }
}