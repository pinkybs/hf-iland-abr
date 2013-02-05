<?php

/** @see Bll_Cache */
require_once 'Bll/Cache.php';

/**
 * Cache For User
 *
 * @package    Bll/Cache
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2010/01/20    Hulj
*/
class Bll_Cache_User
{
    private static $_prefix = 'Cache_User';

    public static function getCacheKey($salt, $params = null)
    {
        return Bll_Cache::getCacheKey(self::$_prefix, $salt, $params);
    }
    
    public static function isAppUser($uid)
    {
        $key = self::getCacheKey('isAppUser', $uid);
        if (!$result = Bll_Cache::get($key)) {
            $dalUser = Dal_Island_User::getDefaultInstance();

            $result = $dalUser->isHaveUser($uid);

            Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_MONTH);
        }

        return $result;
    }
    
    public static function isFibbden($uid)
    {
        $key = self::getCacheKey('isFibbden', $uid);
        $result = Bll_Cache::get($key);
        
        if (!$result) {
            $dalUser = Dal_Island_User::getDefaultInstance();
            $result = 'N';
            $status = $dalUser->getUserStatus($uid);
            if ($status && $status > 0) {
                $result = 'Y';
            }

            Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_MONTH);
        }

        return $result == 'Y';
    }
    
    public static function clearFibbden($uid)
    {
        Bll_Cache::delete(self::getCacheKey('isFibbden', $uid));
    }

    public static function getPerson($uid)
    {
        $key = self::getCacheKey('Person', $uid);
        
        if (!$result = Bll_Cache::get($key)) {
            $dalUser = Dal_Mongo_User::getDefaultInstance();

            $result = $dalUser->getPerson($uid);
            Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
        }

        return $result;
    }

    public static function updatePerson($uid, $user)
    {
        $key = self::getCacheKey('Person', $uid);
        Bll_Cache::set($key, $user, Bll_Cache::LIFE_TIME_ONE_DAY);
    }

    public static function getFriends($uid)
    {
        $key = self::getCacheKey('Friends', $uid);

        if (!$result = Bll_Cache::get($key)) {
            $dalFriend = Dal_Mongo_Friend::getDefaultInstance();

            $result = $dalFriend->getFriends($uid);

            Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_DAY);
        }

        return $result;
    }

    public static function updateFriends($uid, $fids)
    {
        $key = self::getCacheKey('Friends', $uid);
        Bll_Cache::set($key, $fids, Bll_Cache::LIFE_TIME_ONE_DAY);
    }

    public static function isUpdated($uid)
    {
        $key = self::getCacheKey('isUpdated', $uid);

        if (!Bll_Cache::get($key)) {
            return false;
        }

        return true;
    }

    public static function setUpdated($uid)
    {
        $key = self::getCacheKey('isUpdated', $uid);

        Bll_Cache::set($key, '1', Bll_Cache::LIFE_TIME_ONE_HOUR);
    }

    public static function getMixiFriends($uid)
    {
        $key = self::getCacheKey('getMixiFriends1', $uid);
        
        if (!$result = Bll_Cache::get($key)) {
        
            $rest = new Bll_Restful(APP_KEY, APP_SECRET, $uid, APP_ID);
            $friendInfo = $rest->getFriends();
            
            $friends = $friendInfo['friends'];
            if ($friends instanceof osapiPerson) {
                $friendsList = array($friends);
            } else {
                if (!empty($friends)) {
                    $friendsList = $friends->getList();
                }
            }
    
            $result = array();
            
            foreach ($friendsList as $op) {
                $p = $rest->parsePerson($op);
                $result[] = array('uid'=>$p->getId(), 'name'=>$p->getDisplayName(), 'thumbnail'=>$p->getThumbnailUrl());                
            }
            
            Bll_Cache::set($key, $result, Bll_Cache::LIFE_TIME_ONE_MINUTE * 15);
        }
        
        return $result;
    }
    
    public static function cleanPerson($uid)
    {
        Bll_Cache::delete(self::getCacheKey('Person', $uid));
    }

    public static function cleanPeople($ids)
    {
        if (count($ids) > 0) {
            foreach ($ids as $id) {
                self::cleanPerson($id);
            }
        }
    }

    public static function cleanFriends($uid)
    {
        Bll_Cache::delete(self::getCacheKey('Friends', $uid));
    }

    public static function cleanMultiFriends($ids)
    {
        foreach ($ids as $id) {
            self::cleanFriends($id);
        }
    }
}