<?php

/** @see Bll_Cache */
require_once 'Bll/Cache.php';

/**
 * Activity Cache
 *
 * @package    Bll/Cache
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2010/01/20    Liz
*/
class Bll_Cache_Activity
{
    private static $_prefix = 'Bll_Cache_Activity';

    public static function getCacheKey($salt, $params = null)
    {
        return Bll_Cache::getCacheKey(self::$_prefix, $salt, $params);
    }

    /**
     * activity of the user base the activity type
     *
     * @param string $type
     * @param int $uid
     * @return bool
     */
    public static function isSend($type, $uid)
    {
        $key = self::getCacheKey('isSend', array($type, $uid));
        $result = Bll_Cache::get($key);
        $isSend = false;
        if ($result) {
        	$isSend = true;
        }

        return $isSend;
    }

    /**
     * set send flag of the user base the activity type
     *
     * @param string $type
     * @param int $uid
     */
    public static function setSend($type, $uid)
    {
    	$key = self::getCacheKey('isSend', array($type, $uid));
        $time = time();
        $today = date('Y-m-d', $time);
        $lifetime = strtotime($today) + 86400 - $time;
        if ($lifetime < 10) {
        	$lifetime = 10;
        }

        Bll_Cache::set($key, 1, $lifetime);
    }

    public static function deleteSend($type, $uid)
    {
    	$key = self::getCacheKey('isSend', array($type, $uid));
    	Bll_Cache::delete($key);
    }
}