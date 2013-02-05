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
class Dal_Island_Dock extends Dal_Abstract
{
    /**
     * table name
     *
     * @var string
     */
    protected $table_user_dock = 'island_user_dock';
    
    protected $table_user_ship = 'island_user_ship';

    protected $table_dock = 'island_dock';

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
        if ( $tbname == null ) {
            $tbname = $this->table_user_ship;
        }
        $n = $uid % 10;
        return $tbname . '_' . $n;
    }
    
    /**
     * insert user position info
     *
     * @param array $info
     * @return integer
     */
    public function insertDock($info)
    {
        $this->_wdb->insert($this->table_user_dock, $info);
        return $this->_wdb->lastInsertId();
    }

    /**
     * update user position
     *
     * @param string $uid
     * @param array $position
     */
    public function updateUserPosition($uid, $positionId, $newPosition)
    {
        $where = "uid = " .$this->_wdb->quote($uid) . " AND position_id= " . $this->_wdb->quote($positionId);
        $this->_wdb->update($this->table_user_dock, $newPosition, $where);
    }

    /**
     * get user dock info
     *
     * @param integer $uid
     * @return array
     */
    public function getUserDock($uid)
    {
        $sql = "SELECT d.*,s.* FROM $this->table_user_dock AS d LEFT JOIN island_ship AS s ON d.ship_id=s.sid WHERE d.uid=:uid ";

        return $this->_rdb->fetchAll($sql, array('uid'=>$uid));
    }

    /**
     * get user dock info by status
     *
     * @param integer $uid
     * @param integer $status
     * @return array
     */
    public function getUserDockByStatus($uid, $status)
    {
        $sql = "SELECT d.*,s.* FROM $this->table_user_dock AS d LEFT JOIN island_ship AS s ON d.ship_id=s.sid
                WHERE d.status=:status AND d.uid=:uid ";

        return $this->_rdb->fetchAll($sql, array('uid'=>$uid, 'status'=>$status));
    }

    /**
     * get user position list info 
     *
     * @param integer $uid
     * @return array
     */
    public function getUserPositionList($uid)
    {
        $sql = "SELECT d.position_id,d.ship_id,d.receive_time,d.speedup,d.speedup_type,d.status,d.start_visitor_num, 
                d.remain_visitor_num,s.wait_time FROM $this->table_user_dock AS d,island_ship AS s 
                WHERE d.ship_id=s.sid AND d.uid=:uid  ";

        return $this->_rdb->fetchAll($sql, array('uid'=>$uid));
    }
    
    /**
     * get user position info by position id
     *
     * @param integer $uid
     * @param integer $id
     * @return array
     */
    public function getUserPositionById($uid, $id)
    {
        $sql = "SELECT d.*,s.wait_time FROM $this->table_user_dock AS d,island_ship AS s 
                WHERE d.ship_id=s.sid AND d.position_id=:id AND d.uid=:uid  ";

        return $this->_rdb->fetchRow($sql, array('uid'=>$uid, 'id'=>$id));
    }

    /**
     * get user position info by position id
     *
     * @param integer $uid
     * @param integer $id
     * @return array
     */
    public function getUserPositionByIdForupdate($uid, $id)
    {
        $sql = "SELECT ship_id,remain_visitor_num FROM $this->table_user_dock WHERE position_id=:id AND uid=:uid FOR UPDATE ";

        return $this->_rdb->fetchRow($sql, array('uid'=>$uid, 'id'=>$id));
    }
    
    /**
     * get user ship list
     *
     * @param integer $uid
     * @return array
     */
    public function getUserShipList($uid)
    {
        $sql = "SELECT ship_id,count(1) AS count FROM $this->table_user_dock WHERE uid=:uid GROUP BY ship_id ";
        $result = $this->_rdb->fetchAll($sql, array('uid' => $uid));

        $shipList = array();
        for ($i=0,$iCount=count($result); $i<$iCount; $i++) {
            $shipList[$result[$i]['ship_id']] = $result[$i]['count'];
        }
        return $shipList;
    }

    /**
     * get user max ship 
     *
     * @param integer $uid
     * @return int
     */
    public function getUserMaxShip($uid)
    {
        $sql = "SELECT ship_id FROM $this->table_user_dock WHERE uid=:uid ORDER BY ship_id DESC LIMIT 0,1";
        return $this->_rdb->fetchOne($sql, array('uid' => $uid));
    }
    
    /**
     * get user all postition
     *
     * @param integer $uid
     * @return array
     */
	public function getLstUserPostitionById($uid)
    {
		$sql = "SELECT * FROM $this->table_user_dock WHERE uid = :uid ";
		return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }

    /**
     * get add boat check info
     *
     * @param integer $pid
     * @return array
     */
    public function getAddBoatByid($pid)
    {
		$sql = "SELECT * FROM $this->table_dock WHERE pid = :pid";
		return $this->_rdb->fetchRow($sql, array('pid' => $pid));
    }

    /**
     * count user ship_id
     *
     * @param integer $uid
     * @param integer $shipId
     * @return integer
     */
    public function getCntShipIdById($uid, $shipId)
    {
		$sql = "SELECT COUNT(ship_id) FROM $this->table_user_dock WHERE uid = :uid AND ship_id = :shipId";
		return $this->_rdb->fetchOne($sql, array('uid'=>$uid, 'shipId'=>$shipId));
    }

    /**
     * insert user ship info
     *
     * @param array $info
     * @return integer
     */
    public function insertUserShip($info)
    {        
        $tbName = $this->getTableName($info['uid']);
        $this->_wdb->insert($tbName, $info);
        return $this->_wdb->lastInsertId();
    }

    /**
     * insert user ship info
     *
     * @param array $info
     * @return integer
     */
    public function updateUserShip($info)
    {
        $tbName = $this->getTableName($info['uid']);
        $newShipIds = ','.$info['ship_id'];
        $newShipIds = $this->_wdb->quote($newShipIds);
        $sql = "UPDATE $tbName SET ship_id = concat(ship_id, $newShipIds) WHERE position_id=:position_id AND  uid=:uid ";
        $this->_wdb->query($sql,array('uid'=>$info['uid'], 'position_id'=>$info['position_id']));
    }
    
    /**
     * get user unlock ship list
     *
     * @param integer $uid
     * @param integer $pid
     * @return array
     */
    public function getUserUnlockShipList($uid, $pid)
    {
        $tbName = $this->getTableName($uid);
        $sql = "SELECT ship_id FROM $tbName WHERE uid=:uid AND position_id=:pid ";
        $result = $this->_rdb->fetchOne($sql, array('uid'=>$uid, 'pid'=>$pid));
        
        $shipList = explode(',', $result);
        return $shipList;
    }

    /**
     * check user has ship by ship id 
     *
     * @param integer $uid
     * @param integer $pid
     * @param integer $ShipId
     * @return array
     */
    public function hasUserShipById($uid, $pid, $shipId)
    {
        $tbName = $this->getTableName($uid);
        
        $sql = "SELECT ship_id FROM $tbName WHERE uid=:uid AND position_id=:pid ";
        
        $shipList = $this->_rdb->fetchOne($sql, array('uid'=>$uid, 'pid'=>$pid));
        
        $shipList = explode(',', $shipList);
        return in_array($shipId, $shipList);
    }

    /**
     * get user unlock ship list
     *
     * @param integer $uid
     * @param integer $pid
     * @return array
     */
    public function deleteUserShip($uid)
    {
        $tbName = $this->getTableName($uid);
        
        $sql = "DELETE FROM $tbName WHERE uid=:uid ";
        $this->_wdb->query($sql,array('uid'=>$uid));
    }
    
    public function getUserDockImage($uid)
    {
        $sql = "SELECT class_name FROM island_user_background AS a,island_island_background AS b 
                WHERE a.bgid=b.bgid AND uid=:uid AND a.item_type=14";
        
        return $this->_rdb->fetchOne($sql, array('uid'=>$uid));
    }
}