<?php


class Hapyfish2_Island_Dal_Building
{
    protected static $_instance;

    /**
     * Single Instance
     *
     * @return Hapyfish2_Island_Dal_Building
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
    	$id = floor($uid/4) % 50;
    	return 'island_user_building_' . $id;
    }
    
    public function getAllIds($uid)
    {
        $tbname = $this->getTableName($uid);
    	$sql = "SELECT id FROM $tbname WHERE uid=:uid";
    	
        $db = Hapyfish2_Db_Factory::getDB($uid);
        $rdb = $db['r'];
        
        return $rdb->fetchCol($sql, array('uid' => $uid));
    }
    
    public function getOnIslandIds($uid)
    {
        $tbname = $this->getTableName($uid);
    	$sql = "SELECT id FROM $tbname WHERE uid=:uid AND status=1";
    	
        $db = Hapyfish2_Db_Factory::getDB($uid);
        $rdb = $db['r'];
        
        return $rdb->fetchCol($sql, array('uid' => $uid));
    }
    
    public function getAll($uid)
    {
        $tbname = $this->getTableName($uid);
    	$sql = "SELECT id,cid,x,y,z,mirro,item_type,status FROM $tbname WHERE uid=:uid";
    	
        $db = Hapyfish2_Db_Factory::getDB($uid);
        $rdb = $db['r'];
    	
        return $rdb->fetchAssoc($sql, array('uid' => $uid));
    }
    
    public function getOne($uid, $id)
    {
        $tbname = $this->getTableName($uid);
    	$sql = "SELECT id,cid,x,y,z,mirro,item_type,status FROM $tbname WHERE uid=:uid AND id=:id";
    	
        $db = Hapyfish2_Db_Factory::getDB($uid);
        $rdb = $db['r'];
    	
        return $rdb->fetchRow($sql, array('uid' => $uid, 'id' => $id), Zend_Db::FETCH_NUM);
    }
    
    public function getOnIsland($uid)
    {
        $tbname = $this->getTableName($uid);
    	$sql = "SELECT id,cid,x,y,z,mirro,item_type,status FROM $tbname WHERE uid=:uid AND status=1";
    	
        $db = Hapyfish2_Db_Factory::getDB($uid);
        $rdb = $db['r'];
    	
        return $rdb->fetchAll($sql, array('uid' => $uid), Zend_Db::FETCH_NUM);
    }
    
    public function getInWareHouse($uid)
    {
        $tbname = $this->getTableName($uid);
    	$sql = "SELECT id,cid,x,y,z,mirro,item_type,status FROM $tbname WHERE uid=:uid AND status=0";
    	
        $db = Hapyfish2_Db_Factory::getDB($uid);
        $rdb = $db['r'];
    	
        return $rdb->fetchAll($sql, array('uid' => $uid), Zend_Db::FETCH_NUM);
    }
    
    public function upgradeCoordinate($uid, $step = 1)
    {
    	$tbname = $this->getTableName($uid);
    	$sql = "UPDATE $tbname SET x=x+$step,y=y+$step WHERE uid=:uid AND status=1";
    	
    	$db = Hapyfish2_Db_Factory::getDB($uid);
    	$wdb = $db['w'];
    	return $wdb->query($sql, array('uid' => $uid));
    }
    
    public function insert($uid, $building)
    {
        $tbname = $this->getTableName($uid);

        $db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];
        
    	return $wdb->insert($tbname, $building);
    }
    
    public function update($uid, $id, $info)
    {
        $tbname = $this->getTableName($uid);
        
        $db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];
        
    	$uid = $wdb->quote($uid);
        $id = $wdb->quote($id);
    	$where = "uid=$uid AND id=$id";
    	
        return $wdb->update($tbname, $info, $where); 
    }
    
    public function delete($uid, $id)
    {
        $tbname = $this->getTableName($uid);
        
        $sql = "DELETE FROM $tbname WHERE uid=:uid AND id=:id";
        
        $db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];
        
        return $wdb->query($sql, array('uid' => $uid, 'id' => $id));
    }
    
    public function init($uid)
    {
        $tbname = $this->getTableName($uid);
        
        $sql = "INSERT INTO $tbname(uid, id, cid, x, y, z, mirro, status, item_type)
            VALUES 
            (:uid, 1, 6721,  0, 4, 0, 0, 1, 21),
            (:uid, 2, 6721,  1, 7, 0, 0, 1, 21),
            (:uid, 3, 6721,  0, 5, 0, 0, 1, 21),
            (:uid, 4, 6721,  0, 6, 0, 0, 1, 21),
            (:uid, 5, 6721,  5, 0, 0, 0, 1, 21),
            (:uid, 6, 6721,  6, 0, 0, 0, 1, 21),
            (:uid, 7, 7521,  4, 8, 0, 1, 1, 21),
            (:uid, 8, 33421, 6, 2, 0, 0, 1, 21),
            (:uid, 9, 33421, 3, 2, 0, 0, 1, 21),
            (:uid, 10, 33521, 9, 3, 0, 0, 1, 21),
            (:uid, 11, 33521, 9, 6, 0, 0, 1, 21),
            (:uid, 12, 33621, 6, 9, 0, 1, 1, 21)";
        
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
        
        return $wdb->query($sql, array('uid' => $uid));
    }
    
}