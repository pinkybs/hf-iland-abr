<?php

require_once 'Dal/Abstract.php';

/**
 * Island datebase's Operation
 *
 *
 * @package    Dal
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2010/03/09    Liz
 */
class Dal_Island_Visit extends Dal_Abstract
{
    protected static $_instance;
        
    /**
     * table name
     *
     * @var string
     */
    protected $table_user_visit = 'island_user_visit';
    
    /**
     * table name
     *
     * @var string
     */
    protected $table_user_visit_today = 'island_user_visit_today';
    
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }

    /**
     * insert user visit info
     * 
     * @param array $info
     * @return integer
     */
    public function insertUserVisit($info)
    {
        $this->_wdb->insert($this->table_user_visit, $info);
        return $this->_wdb->lastInsertId();
    }

    /**
     * insert user today visit info
     * 
     * @param array $info
     * @return integer
     */
    public function insertUserTodayVisit($info)
    {
        $this->_wdb->insert($this->table_user_visit_today, $info);
        return $this->_wdb->lastInsertId();
    }
    
    /**
     * get user visit info
     *
     * @param integer $uid
     * @param integer $fid
     * @return array
     */
    public function getUserVisitInfo($uid, $fid)
    {
        $sql = "SELECT * FROM $this->table_user_visit WHERE fid=:fid AND uid=:uid ";

        return $this->_rdb->fetchRow($sql, array('fid'=>$fid, 'uid'=>$uid));
    }

    /**
     * get user today visit info
     *
     * @param integer $uid
     * @param integer $fid
     * @return array
     */
    public function getUserTodayVisitInfo($uid, $fid)
    {
        $sql = "SELECT * FROM $this->table_user_visit_today WHERE fid=:fid AND uid=:uid ";

        return $this->_rdb->fetchRow($sql, array('fid'=>$fid, 'uid'=>$uid));
    }
    
}