<?php
/**
 * lei.wu,
 * lei.wu@hapyfish.com
 * */
class Hapyfish2_Island_Event_Dal_Timegift  
{
	protected $tbname = 'island_user_event_timegift';
	protected static $_instance;
	
	public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
	
	public function setup($uid) 
	{
		$a = array('uid' => $uid, 'time_at' => time());
		$db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];
		$wdb->insert($this->tbname, $a);
		return true;
	}
	
	public function nextsteptask($uid) 
	{
		$sql = "UPDATE " . $this->tbname . " SET `state`=state+1, `time_at`='" . time() . "' WHERE uid=:uid";
		$db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];
		$wdb->query($sql,array("uid" => $uid));
		return true;
	}
	
	
}