<?php


class Bll_Island_Log
{
    public static function addInvite($actor, $target, $time, $sig)
    {
        $info = array(
            'actor' => $actor,
            'target' => $target,
            'status' => 1,
        	'sig' => $sig,
            'time' => $time
        );
        try {
            return Dal_Mongo_Invite::getDefaultInstance()->insert($info);
        }catch (Exception $e) {
            err_log($e->getMessage());
        }
        return false;
    }

    public static function addSendGift($actor, $target, $gid, $time, $sig)
    {
        $info = array(
            'actor' => $actor,
            'target' => $target,
            'status' => 1,
            'gid' => $gid,
        	'sig' => $sig,
            'time' => $time
        );

        try {
            return Dal_Mongo_Gift::getDefaultInstance()->insert($info);
        }catch (Exception $e) {
            err_log($e->getMessage());
        }
        return false;

    }
}