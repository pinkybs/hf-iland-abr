<?php

require_once 'Dal/Abstract.php';

/**
 * Island datebase's Operation
 *
 *
 * @package    Dal
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2010/04/12    Liz
 */
class Dal_Island_Gold extends Dal_Abstract
{
    /**
     * table name
     *
     * @var string
     */
    protected $table_user_gold = 'island_user_gold';

    protected static $_instance;

    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * insert user gold info
     *
     * @param array $info
     * @return integer
     */
    public function insertUserGoldInfo($info)
    {
        $this->_wdb->insert($this->table_user_gold, $info);
        return $this->_wdb->lastInsertId();
    }
    
    /**
     * get user gold info
     *
     * @param integer $uid
     * @return array
     */
    public function getUserGoldInfo($uid)
    {
        $sql = "SELECT gold,name,count,create_time FROM island_user_gold WHERE uid=:uid AND create_time>1276869600 ORDER BY create_time DESC";
        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }

    /**
     * get user gold info
     *
     * @param integer $uid
     * @return array
     */
    public function getUserGoldInfoByMonth($uid, $year, $month)
    {
        $startTime = $year.$month.'01';
        if ( $month == 12 ) {
            $endTime = ($year + 1).'0101';
        }
        else {
            $month += 1;
            $month = $month > 9 ? $month : '0'.$month;
            $endTime = $year.$month.'01';
        }
        $startTime = strtotime($startTime);
        $endTime = strtotime($endTime);
        
        $sql = "SELECT gold,name,count,create_time FROM island_user_gold 
                WHERE create_time>= $startTime AND create_time<$endTime AND uid=:uid ORDER BY create_time DESC";
        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }
}