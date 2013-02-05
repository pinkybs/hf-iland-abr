<?php


class Hapyfish2_Island_Dal_Dock
{
    protected static $_instance;

    /**
     * Single Instance
     *
     * @return Hapyfish2_Island_Dal_Dock
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function getTableName($uid)
    {
    	$id = floor($uid/4) % 10;
    	return 'island_user_dock_' . $id;
    }
    
    public function get($uid)
    {
        $tbname = $this->getTableName($uid);
    	$sql = "SELECT position_id,ship_id,receive_time,start_visitor_num FROM $tbname WHERE uid=:uid";
    	
        $db = Hapyfish2_Db_Factory::getDB($uid);
        $rdb = $db['r'];
    	
        return $rdb->fetchAll($sql, array('uid' => $uid), Zend_Db::FETCH_NUM);
    }
    
    public function getPosition($uid, $positionId)
    {
        $tbname = $this->getTableName($uid);
    	$sql = "SELECT position_id,ship_id,receive_time,start_visitor_num FROM $tbname WHERE uid=:uid AND position_id=:position_id";
    	
        $db = Hapyfish2_Db_Factory::getDB($uid);
        $rdb = $db['r'];
    	
        return $rdb->fetchRow($sql, array('uid' => $uid, 'position_id' => $positionId), Zend_Db::FETCH_NUM);
    }
    
    public function changeShip($uid, $positionId, $shipId)
    {
        $tbname = $this->getTableName($uid);
        $sql = "UPDATE $tbname SET ship_id=:ship_id WHERE uid=:uid AND position_id=:position_id";
        
        $db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];
    	
        $wdb->query($sql, array('ship_id' => $shipId, 'uid' => $uid, 'position_id' => $positionId));
    }
    
    public function unlockShip($uid, $positionId, $unlockShipIds)
    {
        $tbname = $this->getTableName($uid);
        $sql = "UPDATE $tbname SET unlock_ship_ids=:unlock_ship_ids WHERE uid=:uid AND position_id=:position_id";
        
        $db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];
    	
        $wdb->query($sql, array('unlock_ship_ids' => $unlockShipIds, 'uid' => $uid, 'position_id' => $positionId));
    }
    
    public function getUnlockShipCount($uid)
    {
        $tbname = $this->getTableName($uid);
    	$sql = "SELECT unlock_ship_ids FROM $tbname WHERE uid=:uid";
    	
        $db = Hapyfish2_Db_Factory::getDB($uid);
        $rdb = $db['r'];
    	
        return $rdb->fetchCol($sql, array('uid' => $uid));
    }
    
    public function getUnlockShipIds($uid, $positionId)
    {
        $tbname = $this->getTableName($uid);
    	$sql = "SELECT unlock_ship_ids FROM $tbname WHERE uid=:uid AND position_id=:position_id";
    	
        $db = Hapyfish2_Db_Factory::getDB($uid);
        $rdb = $db['r'];
    	
        return $rdb->fetchOne($sql, array('uid' => $uid, 'position_id' => $positionId));
    }
    
    public function expandPosition($uid, $positionId, $visitNum = 5)
    {
        $tbname = $this->getTableName($uid);
        $sql = "INSERT IGNORE INTO $tbname (uid, position_id, start_visitor_num, remain_visitor_num) VALUES(:uid, :position_id, :start_visitor_num, :remain_visitor_num)";
        
        $db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];
    	
        return $wdb->query($sql, array('uid' => $uid, 'position_id' => $positionId, 'start_visitor_num' => $visitNum, 'remain_visitor_num' => $visitNum));
    }
    
    public function getPositionCount($uid)
    {
        $tbname = $this->getTableName($uid);
    	$sql = "SELECT COUNT(uid) AS count FROM $tbname WHERE uid=:uid";
    	
        $db = Hapyfish2_Db_Factory::getDB($uid);
        $rdb = $db['r'];
    	
        return $rdb->fetchOne($sql, array('uid' => $uid));
    }
    
    public function update($uid, $id, $info)
    {
        $tbname = $this->getTableName($uid);
        
        $db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];
        
    	$uid = $wdb->quote('uid');
        $id = $wdb->quote('id');
    	$where = "uid=$uid AND id=$id";
    	
        $wdb->update($tbname, $info, $where); 
    }
    
    public function init($uid)
    {
        $time = time() - 1200;
    	$tbname = $this->getTableName($uid);
        $sql = "INSERT INTO $tbname(uid, position_id, receive_time) VALUES(:uid, 1, $time),(:uid, 2, $time),(:uid, 3, $time)";
        
        $db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];
        
        $wdb->query($sql, array('uid' => $uid));
    }
    
    public function clear($uid)
    {
        $tbname = $this->getTableName($uid);
        $sql = "DELETE FROM $tbname WHERE uid=:uid";
        
        $db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];
    	
        $wdb->query($sql, array('uid' => $uid));
    }
    
}