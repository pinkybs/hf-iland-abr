<?php


class Hapyfish2_Island_Dal_Plant
{
    protected static $_instance;

    /**
     * Single Instance
     *
     * @return Hapyfish2_Island_Dal_Plant
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
    	return 'island_user_plant_' . $id;
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
    	$sql = "SELECT id,cid,level,item_id,item_type,x,y,z,mirro,can_find,start_pay_time,wait_visitor_num,delay_time,event,start_deposit,deposit,status FROM $tbname WHERE uid=:uid";
    	
        $db = Hapyfish2_Db_Factory::getDB($uid);
        $rdb = $db['r'];
    	
        return $rdb->fetchAll($sql, array('uid' => $uid), Zend_Db::FETCH_NUM);
    }
    
    public function getOnIsland($uid)
    {
        $tbname = $this->getTableName($uid);
    	$sql = "SELECT id,cid,level,item_id,item_type,x,y,z,mirro,can_find,start_pay_time,wait_visitor_num,delay_time,event,start_deposit,deposit,status FROM $tbname WHERE uid=:uid AND status=1";
    	
        $db = Hapyfish2_Db_Factory::getDB($uid);
        $rdb = $db['r'];
    	
        return $rdb->fetchAll($sql, array('uid' => $uid), Zend_Db::FETCH_NUM);
    }
    
    public function getOneOnIsland($uid, $id)
    {
        $tbname = $this->getTableName($uid);
    	$sql = "SELECT id,cid,level,item_id,item_type,x,y,z,mirro,can_find,start_pay_time,wait_visitor_num,delay_time,event,start_deposit,deposit,status FROM $tbname WHERE id=:id AND uid=:uid AND status=1";
    	
        $db = Hapyfish2_Db_Factory::getDB($uid);
        $rdb = $db['r'];
    	
        return $rdb->fetchRow($sql, array('id' => $id, 'uid' => $uid), Zend_Db::FETCH_NUM);
    }
    
    public function getOne($uid, $id)
    {
        $tbname = $this->getTableName($uid);
    	$sql = "SELECT id,cid,level,item_id,item_type,x,y,z,mirro,can_find,start_pay_time,wait_visitor_num,delay_time,event,start_deposit,deposit,status FROM $tbname WHERE id=:id AND uid=:uid";
    	
        $db = Hapyfish2_Db_Factory::getDB($uid);
        $rdb = $db['r'];
    	
        return $rdb->fetchRow($sql, array('id' => $id, 'uid' => $uid), Zend_Db::FETCH_NUM);
    }
    
    public function getMultiOnIsland($uid, $ids)
    {
        $tbname = $this->getTableName($uid);
    	$sql = "SELECT id,cid,level,item_id,item_type,x,y,z,mirro,can_find,pay_time,ticket,start_pay_time,wait_visitor_num,delay_time,event,start_deposit,deposit,status FROM $tbname WHERE uid=:uid AND status=1 AND id in ($ids)";
    	
        $db = Hapyfish2_Db_Factory::getDB($uid);
        $rdb = $db['r'];
    	
        return $rdb->fetchAll($sql, array('uid' => $uid), Zend_Db::FETCH_NUM);
    }
    
    public function getInWareHouse($uid)
    {
        $tbname = $this->getTableName($uid);
    	$sql = "SELECT id,cid,level,item_id,item_type,x,y,z,mirro,can_find,start_pay_time,wait_visitor_num,delay_time,event,start_deposit,deposit,status FROM $tbname WHERE uid=:uid AND status=0";
    	
        $db = Hapyfish2_Db_Factory::getDB($uid);
        $rdb = $db['r'];
    	
        return $rdb->fetchAll($sql, array('uid' => $uid), Zend_Db::FETCH_NUM);
    }
    
    public function getTopLevelGroupByItem($uid)
    {
        $tbname = $this->getTableName($uid);
    	$sql = "SELECT item_id,level FROM (SELECT item_id,level FROM $tbname WHERE uid=:uid ORDER BY level DESC) AS c GROUP BY item_id";
    	
        $db = Hapyfish2_Db_Factory::getDB($uid);
        $rdb = $db['r'];
    	
        return $rdb->fetchPairs($sql, array('uid' => $uid));
    }
    
    public function getAllByItemKind($uid)
    {
        $tbname = $this->getTableName($uid);
    	$sql = "SELECT item_id,level FROM $tbname WHERE uid=:uid ORDER BY level DESC";
    	
        $db = Hapyfish2_Db_Factory::getDB($uid);
        $rdb = $db['r'];
    	
        return $rdb->fetchAll($sql, array('uid' => $uid), Zend_Db::FETCH_NUM);
    }
    
    public function insert($uid, $plant)
    {
        $tbname = $this->getTableName($uid);

        $db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];
        
    	$wdb->insert($tbname, $plant);
        return $wdb->lastInsertId();
    }
    
    public function update($uid, $id, $info)
    {
        $tbname = $this->getTableName($uid);
        
        $db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];
        
    	$uid = $wdb->quote($uid);
        $id = $wdb->quote($id);
    	$where = "uid=$uid AND id=$id";
    	
        $wdb->update($tbname, $info, $where); 
    }
    
    public function delete($uid, $id)
    {
        $tbname = $this->getTableName($uid);
        
        $sql = "DELETE FROM $tbname WHERE uid=:uid AND id=:id";
        
        $db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];
        
        $wdb->query($sql, array('uid' => $uid, 'id' => $id));
    }
    
    public function init($uid)
    {
        $time = time();
        $payTime = $time - 3600;
        $payTime2 = $time - 3420;
    	$tbname = $this->getTableName($uid);
        
        $sql = "INSERT INTO $tbname(uid,id,cid,level,item_id,item_type,x,y,z,mirro,status,buy_time,start_pay_time,start_deposit,deposit)
             VALUES 
             (:uid, 1, 632,   1, 6,   32, 6, 7, 0, 0, 1, $time, $payTime, 300, 300),
             (:uid, 2, 21232, 1, 212, 32, 4, 1, 0, 0, 1, $time, $payTime2, 100, 100)";
        
        $db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];
        
        $wdb->query($sql, array('uid' => $uid));
    }
    
    public function upgradeCoordinate($uid, $step = 1)
    {
    	$tbname = $this->getTableName($uid);
    	$sql = "UPDATE $tbname SET x=x+$step,y=y+$step WHERE uid=:uid AND status=1";
    	
    	$db = Hapyfish2_Db_Factory::getDB($uid);
    	$wdb = $db['w'];
    	return $wdb->query($sql, array('uid' => $uid));
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