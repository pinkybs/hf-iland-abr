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
class Dal_Island_Rank extends Dal_Abstract
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
    public function getUserFriendsAll($fids)
    {
        $now = time();
        $fids = $this->_rdb->quote($fids);
        $sql = "SELECT uid,exp,level,boat_arrive_time 
                FROM $this->table_user WHERE uid IN ($fids) ";
        $result = $this->_rdb->fetchAll($sql);
       
        //$expSort = array();
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
            
            //$expSort[$key] = $value['exp'];
        }
        //array_multisort($expSort, SORT_DESC, $friendList);
        return $friendList;
    }
    
    /**
     * get user in game friend info
     * @param array $fids
     * @return array
     */
    public function getUserFriends($fids, $pageIndex, $pageSize)
    {
        $start = ($pageIndex - 1)*$pageSize;
        $now = time();
        $fids = $this->_rdb->quote($fids);
        $sql = "SELECT uid,exp,level,praise AS praise,coin,power,gold AS money,IF($now>boat_arrive_time, 1, 0) AS canSteal
                FROM $this->table_user WHERE uid IN ($fids) ORDER BY exp DESC LIMIT $start,$pageSize ";
        return $this->_rdb->fetchAll($sql);
    }

    /**
     * get user in game friend uid
     *
     * @param array $fids
     * @return array
     */
    public function getUserJoinFriends($fids)
    {
        $fids = $this->_rdb->quote($fids);
        $sql = "SELECT uid FROM $this->table_user WHERE uid IN ($fids)";
        return $this->_rdb->fetchAll($sql);
    }

    /**
     * get user rank
     * @param string $type
     * @return array
     */
    public function getUserRank($type)
    {
        $now = time();
        $sql = "SELECT uid,exp,level,praise AS praise,coin,power,gold AS money,IF( $now > boat_arrive_time, 1, 0) AS canSteal
                FROM $this->table_user WHERE uid > 1 ORDER BY $type DESC LIMIT 0,100";

        return $this->_rdb->fetchAll($sql);
    }

    /**
     * get my rank
     *
     * @param string $uid
     * @param string $type :coin or exp
     * @param integer $myCol :coin or exp (value)
     * @param array $fids
     * @return integer
     */
    public function getMyRank($uid, $type, $myCol, $fids)
    {
        $fids = $this->_rdb->quote($fids);

        $sql = "SELECT (COUNT(uid)+1) AS myRank FROM $this->table_user
                WHERE $type > $myCol AND uid IN ($fids)";

        return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }

    /**
     * get my columns info
     * @param : integer $uid
     * @param string $type :coin or exp
     * @return: integer
     */
    public function getMyType($uid, $type)
    {
        $sql = "SELECT $type FROM $this->table_user WHERE uid=:uid";
        return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }

    /**
     * get my rank in alll
     *
     * @param string $type :coin or exp
     * @param integer $myCol :coin or exp (value)
     * @return integer
     */
    public function getMyRankAll($type, $myCol)
    {
        $sql = "SELECT (COUNT(uid)+1) AS myRank FROM $this->table_user
                WHERE $type > $myCol";

        return $this->_rdb->fetchOne($sql);
    }
}