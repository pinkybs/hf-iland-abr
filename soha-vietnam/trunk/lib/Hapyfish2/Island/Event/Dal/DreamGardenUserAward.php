<?php
/**
 * bujisky.li
 * bujisky.li@hapyfish.com
 * */
class Hapyfish2_Island_Event_Dal_DreamGardenUserAward  
{
	protected $tbname = 'island_user_event_dreamgarden';
	protected static $_instance;
	
	public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
	
	public function insert($uid) 
	{
		$db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];
        $info = array('uid' => $uid, 'time_at' => time());
    	return $wdb->insert($this->tbname, $info); 
	}
	public function get($uid) 
	{
    	$sql = "SELECT uid FROM ".$this->tbname." WHERE uid=:uid";
    	
        $db = Hapyfish2_Db_Factory::getDB($uid);
        $rdb = $db['r'];
    	
        return $rdb->fetchOne($sql, array('uid' => $uid));
	}
	
	public function delete($uid) 
	{
    	$sql = "DELETE FROM ".$this->tbname." WHERE uid=:uid";
    	
        $db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];
        
        return $wdb->query($sql, array('uid' => $uid));
	}
	
}