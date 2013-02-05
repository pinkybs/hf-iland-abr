<?php

class Hapyfish_Island_Dal_Dock extends Hapyfish_Island_Dal_Abstract
{
	protected $table_user_dock = 'island_user_dock';
	protected $table_user_ship = 'island_user_ship';
	protected $table_ship = 'island_ship';
	protected $table_dock = 'island_dock';

    protected static $_instance;

    /**
     * Single Instance
     *
     * @return Hapyfish_Island_Dal_Dock
     */
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
     * get user ship list
     *
     * @param integer $uid
     * @return array
     */
    public function getUserShipList($uid)
    {
        $sql = "SELECT ship_id,count(1) AS count FROM $this->table_user_dock WHERE uid=:uid GROUP BY ship_id";
        $result = $this->_rdb->fetchAll($sql, array('uid' => $uid));

        $shipList = array();
        
        if ($result) {
        	foreach ($result as $ship) {
        		$shipList[$ship['ship_id']] = $ship['count'];
        	}
        }

        return $shipList;
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
        $sql = "SELECT ship_id FROM $tbName WHERE uid=:uid AND position_id=:pid";
        $result = $this->_rdb->fetchOne($sql, array('uid'=>$uid, 'pid'=>$pid));
        
        $shipList = explode(',', $result);
        return $shipList;
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
                WHERE d.ship_id=s.sid AND d.uid=:uid";

        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }
    
    /**
     * get ship info by ship id
     *
     * @param integer $id
     * @return array
     */
    public function getShip($id)
    {
        $sql = "SELECT * FROM $this->table_ship WHERE sid=:id";

        return $this->_rdb->fetchRow($sql,array('id' => $id));
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
     * get add visitore count by praise
     *
     * @param integer $praise
     * @param integer $shipId
     * @return array
     */
    public function getShipAddVisitorByPraise($shipId, $praise)
    {
        $sql = "SELECT max(num) FROM island_praise_ship WHERE praise <= :praise AND sid=:sid";

        return $this->_rdb->fetchOne($sql, array('praise' => $praise, 'sid' => $shipId));
    }
    
}