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
class Dal_Island_Feed extends Dal_Abstract
{
    /**
     * table name
     *
     * @var string
     */
    protected $table_minifeed = 'island_minifeed';

    /**
     * table name
     *
     * @var string
     */
    protected $table_newsfeed = 'island_newsfeed';
    
    /**
     * table name
     *
     * @var string
     */
    protected $table_notice = 'island_notice';
    
    protected static $_instance;

    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * insert minifeed
     *
     * @param array $info
     * @return integer
     */
    public function insertMinifeed($info)
    {
        $this->_wdb->insert($this->table_minifeed, $info);
        return $this->_wdb->lastInsertId();
    }

    /**
     * insert newsfeed
     *
     * @param array $info
     * @return integer
     */
    public function insertNewsfeed($info)
    {
        $this->_wdb->insert($this->table_newsfeed, $info);
        return $this->_wdb->lastInsertId();
    }

    /**
     * update user feed status
     *
     * @param integer $uid
     * @return void
     */
    public function updateFeedStatus($uid)
    {
        $sql = "UPDATE $this->table_minifeed SET status=1 WHERE uid=:uid ";
        $this->_wdb->query($sql,array('uid'=>$uid));
    }

    /**
     * get user new minifeed count
     *
     * @param integer $uid
     * @return integer
     */
    public function getNewMiniFeedCount($uid)
    {
        $sql = "SELECT COUNT(1) FROM $this->table_minifeed WHERE uid=:uid AND status=0 ";
        return $this->_rdb->fetchOne($sql, array('uid'=>$uid));
    }
    
    /**
     * get minifeed
     *
     * @param integer $uid
     * @param integer $pageIndex
     * @param integer $pageSize
     * @return array
     */
    public function getMinifeed($uid, $pageIndex=1, $pageSize=10)
    {
        $start = ($pageIndex - 1) * $pageSize;
        $sql = "SELECT * FROM $this->table_minifeed WHERE uid=:uid ORDER BY create_time DESC LIMIT $start,$pageSize";
        return $this->_rdb->fetchAll($sql, array('uid'=>$uid));
    }

    /**
     * get newsfeed
     *
     * @param integer $uid
     * @param integer $pageIndex
     * @param integer $pageSize
     * @return array
     */
    public function getNewsfeed($uid, $fids, $pageIndex=1, $pageSize=10)
    {
       $fids = $this->_rdb->quote($fids); 
       $start = ($pageIndex - 1) * $pageSize;
       $sql = "SELECT p1.* FROM $this->table_newsfeed AS p1,
               (SELECT * FROM $this->table_newsfeed WHERE uid IN ($fids) AND target<>:uid AND actor<>:uid
               GROUP BY create_time ORDER BY create_time DESC LIMIT $start,$pageSize) AS p2
               WHERE p1.id=p2.id ORDER BY p1.create_time DESC";
        return $this->_rdb->fetchAll($sql, array('uid'=>$uid));
    }

    /**
     * get notice list
     *
     * @return array
     */
    public function getNoticeList()
    {
        $sql = "SELECT id,title,create_time FROM $this->table_notice ORDER BY id ";
        return $this->_rdb->fetchAll($sql);
    }
}