<?php

require_once 'Dal/Abstract.php';

/**
 * Island datebase's Operation
 *
 *
 * @package    Dal
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2010/03/08    Liz
 */
class Dal_Island_Test extends Dal_Abstract
{
    protected static $_instance;

    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function getTableName($uid, $tbname = null)
    {
        $n = $uid % 10;
        return $tbname . '_' . $n;
    }
    
    public function insertUserFifa($info)
    {
        $this->_wdb->insert('tmp_user_fifa', $info);
    }
    
    public function getUserFifaList($pageIndex, $pageSize)
    {
    	$start = ($pageIndex - 1) * $pageSize;
        $sql = "SELECT uid FROM tmp_user_fifa LIMIT $start,$pageSize";
        return $this->_rdb->fetchAll($sql);
    }

    public function deleteUserFifa($uid)
    {
        $sql = "DELETE FROM tmp_user_fifa WHERE uid=:uid";
        $this->_wdb->query($sql, array('uid'=>$uid));
    }
    
    public function updateFifaResult($id, $status)
    {
        $sql = "UPDATE island_fifa SET status=:status WHERE id=:id";
        $this->_wdb->query($sql, array('id'=>$id, 'status'=>$status));
    }
    
    /**
     * clean user island,background, building, plant
     *
     * @param integer $uid
     * @return array
     */
    public function cleanUserIsland($uid)
    {
        $sql = "DELETE FROM island_user_background WHERE uid=:uid";
        $this->_wdb->query($sql, array('uid'=>$uid));

        $buildingName = $this->getTableName($uid, 'island_user_building');
        $sql = "DELETE FROM $buildingName WHERE uid=:uid";
        $this->_wdb->query($sql, array('uid'=>$uid));
        
        $plantName = $this->getTableName($uid, 'island_user_plant');
        $sql = "DELETE FROM $plantName WHERE uid=:uid";
        $this->_wdb->query($sql, array('uid'=>$uid));
    }

    /**
     * update user new plant info
     *
     * @param integer $uid
     * @return array
     */
    public function updateNewPlant($uid)
    {
        $tbname = $this->getTableName($uid, 'island_user_plant');
        $sql = "update $tbname set event=0,wait_visitor_num=0,deposit=0,start_deposit=0 where uid=:uid";
        $this->_wdb->query($sql, array('uid'=>$uid));
    }

    /**
     * delete user feed info
     *
     * @param integer $uid
     * @return void
     */
    public function deleteMinifeed($uid)
    {
        $this->_mg->renren_island->minifeed->remove(array('uid' => $uid));
    }

    /**
     * get user list
     *
     * @param integer $start
     * @param integer $end
     * @return array
     */
    public function getUserList($start, $end)
    {
        $sql = "SELECT uid FROM island_user ORDER BY id LIMIT $start,$end";

        return $this->_rdb->fetchAll($sql);
    }
    /**
     * get user list
     *
     * @param integer $start
     * @param integer $end
     * @return array
     */
    public function getUserPositionCount($uid)
    {
        $sql = "SELECT position_count FROM island_user WHERE uid=:uid ";
        return $this->_rdb->fetchOne($sql, array('uid'=>$uid));
    }

    /**
     * get user list
     *
     * @param integer $start
     * @param integer $end
     * @return array
     */
    public function getShipId($uid, $pid)
    {
        $sql = "SELECT ship_id FROM island_user_dock WHERE uid=:uid AND position_id=:pid ";
        return $this->_rdb->fetchOne($sql, array('uid'=>$uid, 'pid'=>$pid));
    }
    
    public function getUserPlantPraise($uid)
    {
    	$tableName = $this->getTableName($uid, 'island_user_plant');
        $sql = "select sum(b.add_praise) from $tableName as u,island_island_plant as b 
                where u.bid=b.bid and u.uid=:uid and u.status=1;";
        return $this->_rdb->fetchOne($sql, array('uid'=>$uid));
    }

    public function getUserBuildingPraise($uid)
    {
        $tableName = $this->getTableName($uid, 'island_user_building');
        $sql = "select sum(b.add_praise) from $tableName as u,island_island_building as b 
                where u.bid=b.bid and u.uid=:uid and u.status=1";
        return $this->_rdb->fetchOne($sql, array('uid'=>$uid));
    }

    public function getUserBackgroundPraise($uid)
    {
        $sql = "select sum(b.add_praise) from island_user_background as u,island_island_background as b 
                where u.bgid=b.bgid and u.uid=:uid and u.status=1";
        return $this->_rdb->fetchOne($sql, array('uid'=>$uid));
    }
}