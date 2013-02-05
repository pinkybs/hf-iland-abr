<?php

require_once 'Dal/Abstract.php';

/**
 * Island datebase's Operation
 *
 *
 * @package    Dal
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2010/01/20    Liz
 */
class Dal_Island_Friend extends Dal_Abstract
{
    /**
     * table name
     *
     * @var string
     */
    protected $table_user = 'island_user';

    protected static $_instance;

    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * get user in game friend info
     * @param array $fids
     * @return array
     */
    public function getUserFriends($fids)
    {
        $now = time();
        $fids = $this->_rdb->quote($fids);
        $sql = "SELECT uid,exp,level,boat_arrive_time 
                FROM $this->table_user WHERE uid IN ($fids) ";
        $result = $this->_rdb->fetchAll($sql);
        
        $friendList = array();
        foreach ( $result as $value ) {
            if ( $now > $value['boat_arrive_time'] ) {
                $value['canSteal'] = 1;
            }
            else {
                $value['canSteal'] = 0;
            }
            unset($value['boat_arrive_time']);
            $friendList[] = $value;
        }
        return $friendList;
    }
}