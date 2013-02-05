<?php
/**
 * lei.wu,
 * lei.wu@hapyfish.com,
 * 2011-4-29
 * */
class Hapyfish2_Island_Dal_Hash
{
	protected static $_instance;
	
	protected static $_tbname = 'island_hash';
	
	/**
     * Single Instance
     *
     * @return Hapyfish2_Island_Dal_Card
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    // 获得 val
    public function get($key)
    {
    	$sql = "SELECT `key`,`val` FROM " . self::$_tbname . " WHERE `key`=:key";
    	
    	$db = Hapyfish2_Db_Factory::getBasicDB('db_0');
    	$wdb = $db['w'];
    	
        return $wdb->fetchPairs($sql, array('key' => $key));
    }
    
    // 设置 key,val
    public function set($key, $val)
    {
		$sql = "INSERT INTO " . self::$_tbname . " (`key`, `val`) VALUES('{$key}', '{$val}' ) ON DUPLICATE KEY UPDATE `val`='{$val}'";

		$db = Hapyfish2_Db_Factory::getBasicDB('db_0');
    	$wdb = $db['w'];

        $wdb->query($sql);
    }
    
    // 清除 key
    public function clear($key)
    {
    	$sql = "DELETE FROM " . self::$_tbname . " WHERE `key`='{$key}'";
    	
    	$db = Hapyfish2_Db_Factory::getBasicDB('db_0');
    	$wdb = $db['w'];
    	
    	$wdb->query($sql);
    }
    
    
}