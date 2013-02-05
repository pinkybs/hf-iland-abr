<?php

/** @see Bll_Cache */
require_once 'Bll/Cache.php';

/**
 * Cache For User
 *
 * @package    Bll/Cache
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2010/05/05    Hulj
*/
class Bll_Cache_Nonce
{
    private static $_prefix = 'Bll_Cache_Nonce';

    public static function getCacheKey($salt, $params = null)
    {
        return Bll_Cache::getCacheKey(self::$_prefix, $salt, $params);
    }

    public static function createNonce($nonce, $data)
    {
        $key = self::getCacheKey('nonce', $nonce);
        Bll_Cache::set($key, $data, Bll_Cache::LIFE_TIME_ONE_MINUTE);
    }

    public static function getNonce($nonce)
    {
        $key = self::getCacheKey('nonce', $nonce);
        return Bll_Cache::get($key);
    }

}