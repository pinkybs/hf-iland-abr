<?php
/**
 * lei.wu,
 * lei.wu@hapyfish.com
 * */
class Hapyfish2_Island_Event_Dal_Qixi
{
	protected static $_instance;
	protected $tbname = 'island_user_event_qxi2_gift';
	public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
	
    public function getUserStatue($uid){
    	
    	$db = Hapyfish2_Db_Factory::getDB($uid);
        $rdb = $db['r'];
        $sql = "SELECT status from $this->tbname where uid=:uid";
        return $rdb->fetchOne($sql, array('uid' => $uid));
    }
    
    public function updateUserStatus($uid)
    {
    	$db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];
        $info = array(
        	'uid' => $uid,
        	'status' => 1,
        	'create_time' => time()
        );
        return $wdb->insert($this->tbname, $info); 	
    }
    
    public function delete($uid)
    {
    	$sql = "DELETE FROM $this->tbname where uid=$uid";
    	$db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];
        $wdb->query($sql);
    }

}