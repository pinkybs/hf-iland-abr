<?php

require_once 'Dal/Abstract.php';

class Dal_Mongo_Visit extends Dal_Mongo_Abstract
{        
    protected static $_instance;
        
    /**
     * single instance of Dal_Mongo_Visit
     *
     * @return Dal_Mongo_Visit
     */
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
     * @return boolean
     */
    public function insertUserVisit($info)
    {
        return $this->_mg->mixi_island->user_visit->insert($info);
    }

    /**
     * insert user today visit info
     * 
     * @param array $info
     * @return boolean
     */
    public function insertUserTodayVisit($info, $todayDate)
    {
        $dataBase = 'user_visit_' . $todayDate;
        return $this->_mg->mixi_island->$dataBase->insert($info);
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
        $result = $this->_mg->mixi_island->user_visit->findOne(array('uid' => $uid, 'fid' => $fid));
        
        if ($result) {
            return $result;
        }
        
        return false;
    }

    /**
     * get user today visit info
     *
     * @param integer $uid
     * @param integer $fid
     * @return array
     */
    public function getUserTodayVisitInfo($uid, $fid, $todayDate)
    {        
        $dataBase = 'user_visit_' . $todayDate;
        $result = $this->_mg->mixi_island->$dataBase->findOne(array('uid' => $uid, 'fid' => $fid));
        
        if ($result) {
            return $result;
        }
        
        return false;
    }

    /**
     * delete user feed info
     *
     * @param integer $uid
     * @return void
     */
    public function deleteVisit($uid)
    {        
        $todayDate = date('Ymd');
        $name = 'user_visit_' . $todayDate;
        $this->_mg->mixi_island->$name->remove(array('uid' => $uid));
        
        $this->_mg->mixi_island->user_visit->remove(array('uid' => $uid));
    }
}