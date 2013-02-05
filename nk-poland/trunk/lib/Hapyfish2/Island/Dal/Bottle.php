<?php
/**
 * lei.wu,
 * lei.wu@hapyfish.com,
 * 2011-4-29
 * */
class Hapyfish2_Island_Dal_Bottle
{
	
	protected static $_instance;
	
	protected static $_tbname = 'island_bottle';
	
	public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    // 创建新季物品时，用来初始化bottle 表数据
    public function initRows($btl_id, $num)
    {
    	$db = Hapyfish2_Db_Factory::getBasicDB('db_0');
    	$wdb = $db['w'];
		
		$info = array('btl_id'=>$btl_id, 'btl_name'=>'', 'btl_tips'=>'');
		
		for ($i=0; $i<$num; $i++) {
			$wdb->insert(self::$_tbname, $info);
		}
    }
    
    // 获得一季里所有物品
    public function getAllByBottleId($btl_id) 
    {
    	$db = Hapyfish2_Db_Factory::getBasicDB('db_0');
    	$rdb = $db['r'];
    	
    	$sql = "SELECT id, btl_id, btl_name, btl_tips, 
    	type, coin, gold, item_id, odds, num, starfish 
    	FROM " . self::$_tbname . " WHERE btl_id=:btl_id
    	ORDER BY id ASC";
    	
    	return $rdb->fetchAssoc($sql, array('btl_id'=>$btl_id));
    }
    
    // 获得单行详细数据
    public function getInfo($id)
    {
    	$db = Hapyfish2_Db_Factory::getBasicDB('db_0');
    	$rdb = $db['r'];
    	
    	$sql = "SELECT id, btl_id, btl_name, btl_tips, 
    	type, coin, gold, item_id, odds, num, starfish 
    	FROM " . self::$_tbname . " WHERE id=:id
    	ORDER BY id ASC";
    	
    	return $rdb->fetchRow($sql, array('id'=>$id));
    }
    
    // 更新单行数据
    public function update($id, $info)
    {
    	$db = Hapyfish2_Db_Factory::getBasicDB('db_0');
    	$wdb = $db['w'];
    	
    	$where = $wdb->quoteInto('id = ?', $id);
    	return $wdb->update(self::$_tbname, $info, $where);
    }
    
    // 删除单行数据
    public function delete($id)
    {
    	$db = Hapyfish2_Db_Factory::getBasicDB('db_0');
    	$wdb = $db['w'];
    	
    	$where = $wdb->quoteInto('id = ?', $id);
    	return $wdb->delete(self::$_tbname, $where);
    }
    
}