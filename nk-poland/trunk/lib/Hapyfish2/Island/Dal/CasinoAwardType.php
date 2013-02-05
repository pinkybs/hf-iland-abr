<?php
class Hapyfish2_Island_Dal_CasinoAwardType
{
	protected static $_instance;
	
	protected static $_tbname = 'island_casino_award_type';
	
	public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    public function getAll()
    {
    	$sql = "SELECT `id`,`gid`,`name`,`type`,`coin`,
    	`gold`,`lv_point`,`item_cid`,`odds`,`num` 
    	FROM " . self::$_tbname . " ORDER BY gid ASC";
    	
    	$db = Hapyfish2_Db_Factory::getBasicDB('db_0');
    	$rdb = $db['r'];
    	
    	return $rdb->fetchAssoc($sql);
    }
    
    public function getInfo($id)
    {
    	$sql = "SELECT `id`,`gid`,`name`,`type`,`coin`,
    	`gold`,`lv_point`,`item_cid`,`odds`,`num` 
    	FROM " . self::$_tbname . " WHERE id=:id";
    	
    	$db = Hapyfish2_Db_Factory::getBasicDB('db_0');
    	$rdb = $db['r'];
    	
    	return $rdb->fetchRow($sql, array('id'=>$id));
    }
    
    public function delete($id)
    {
    	$db = Hapyfish2_Db_Factory::getBasicDB('db_0');
    	$wdb = $db['w'];
    	
    	$where = $wdb->quoteInto('id = ?', $id);
    	return $wdb->delete(self::$_tbname, $where);
    }
    
    public function update($id, $info)
    {
    	$db = Hapyfish2_Db_Factory::getBasicDB('db_0');
    	$wdb = $db['w'];
    	
    	$where = $wdb->quoteInto('id = ?', $id);
    	return $wdb->update(self::$_tbname, $info, $where);
    }
	
}