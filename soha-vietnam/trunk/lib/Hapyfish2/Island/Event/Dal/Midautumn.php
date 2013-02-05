<?php

class Hapyfish2_Island_Event_Dal_Midautumn
{
    protected static $_instance;

    /**
     * Single Instance
     *
     * @return Hapyfish2_Island_Event_Dal_Valentine
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function getUserPass($uid)
    {
        $db = Hapyfish2_Db_Factory::getDB($uid);
        $rdb = $db['r'];
		$sql = "SELECT pass FROM island_user_event_guoqing WHERE uid=:uid";
        return $rdb->fetchOne($sql, array('uid' => $uid));
    }
    
    public function update($uid, $pass)
    {
    	$db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];
        $sql = "INSERT INTO island_user_event_guoqing VALUES($uid, $pass) ON DUPLICATE KEY UPDATE pass=:pass";
        return $wdb->query($sql, array('pass' => $pass));
    }
}