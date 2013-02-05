<?php

require_once 'Bll/Cache/User.php';

class Bll_Friend
{
    public static function getFriendIds($uid)
    {
        $fids = self::getFriends($uid);
        
        if (empty($fids)) {
            return '';
        }
        
        return implode(',', $fids);
    }
    
    public static function getFriends($uid)
    {        
        return Bll_Cache_User::getFriends($uid);
    }

    protected static function getDalInstance()
    {
        return Dal_Mongo_Friend::getDefaultInstance();
    }
    
    public static function getFriendsPage($uid, $page = 1, $step = 10)
    {
        $fids = Bll_Cache_User::getFriends($uid);

        if ($fids) {
            $start = ($page -1) * $step;
            $count = count($fids);
            if ($count > 0 && $start < $count) {
                return array_slice($fids, $start, $step);
            }
        }
        
        return null;
    }
    
    public static function isFriend($uid, $fid)
    {
        $fids = self::getFriends($uid);
        
        if (empty($fids)) {
            return false;
        }
                
        return in_array($fid, $fids);
    }
        
    public static function updateFriends($uid, $fids)
    {
        $dalFriend = self::getDalInstance();
        
        try {
            $dalFriend->insertFriend($uid, $fids);

            Bll_Cache_User::cleanFriends($uid);
        }
        catch (Exception $e) {
            err_log($e->getMessage());
        }
    }
    
}