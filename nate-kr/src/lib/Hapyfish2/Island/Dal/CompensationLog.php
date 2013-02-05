<?php

class Hapyfish2_Island_Dal_CompensationLog
{
    protected static $_instance;

    /**
     * Single Instance
     *
     * @return Hapyfish2_Island_Dal_CompensationLog
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
    	return 'island_user_compensationlog_' . $id;
    }
    
    public function getAll($uid)
    {
    	$tbname = $this->getTableName($uid);
    	$sql = "SELECT id,uid,create_time FROM $tbname WHERE uid=:uid";
    	
        $db = Hapyfish2_Db_Factory::getDB($uid);
        $rdb = $db['r'];
    	
        return $rdb->fetchAll($sql, array('uid' => $uid));
    }
    
    public function getOne($uid, $id)
    {
    	$tbname = $this->getTableName($uid);
    	$sql = "SELECT uid FROM $tbname WHERE uid=:uid AND id=:id";
    	
        $db = Hapyfish2_Db_Factory::getDB($uid);
        $rdb = $db['r'];
    	
        return $rdb->fetchOne($sql, array('uid' => $uid, 'id' => $id));
    }
    
    public function insert($uid, $info)
    {
    	$tbname = $this->getTableName($uid);

        $db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];
        
    	return $wdb->insert($tbname, $info); 	
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