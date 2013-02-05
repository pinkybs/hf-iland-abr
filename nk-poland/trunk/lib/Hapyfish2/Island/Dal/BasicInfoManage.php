<?php

class Hapyfish2_Island_Dal_BasicInfoManage
{
    protected static $_instance;
    
    protected function getDB()
    {
    	$key = 'db_0';
    	return Hapyfish2_Db_Factory::getBasicDB($key);
    }

    /**
     * Single Instance
     *
     * @return Hapyfish2_Island_Dal_BasicInfoManage
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    public function addNotice($info)
    {
		$tbname = 'island_notice';

        $db = $this->getDB();
        $wdb = $db['w'];
        
    	$wdb->insert($tbname, $info);
        return $wdb->lastInsertId();
    }
    
    public function updateNotice($id, $info)
    {
        $tbname = 'island_notice';
        
        $db = $this->getDB();
        $wdb = $db['w'];
    	$where = $wdb->quoteinto('id = ?', $id);
    	
        $wdb->update($tbname, $info, $where);
    }
    
    public function deleteNotice($id)
    {
        $tbname = 'island_notice';
        
        $sql = "DELETE FROM $tbname WHERE id=:id";
        
        $db = $this->getDB();
        $wdb = $db['w'];
        
        $wdb->query($sql, array('id' => $id));
    }
    
    public function getList()
    {
    	$sql = "SELECT id,title,position,priority,link,opened,create_time FROM island_notice ORDER BY id";
    	
        $db = $this->getDB();
        $rdb = $db['r'];
    	
        return $rdb->fetchAssoc($sql);
    }
    
}