<?php


class Hapyfish2_Island_Dal_UserSequence
{
    protected static $_instance;

    /**
     * Single Instance
     *
     * @return Hapyfish2_Island_Dal_UserSequence
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
    	return 'island_user_seq_' . $id;
    }
    
    public function get($uid, $name, $step = 1)
    {
        $tbname = $this->getTableName($uid);
    	$sql = "UPDATE $tbname SET id=LAST_INSERT_ID(id+$step) WHERE uid=:uid AND `name`=:name";
    	
        $db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];
        
        $wdb->query($sql, array('uid' => $uid, 'name' => $name));
    	
        return $wdb->lastInsertId();
    }
    
    public function init($uid)
    {
        $tbname = $this->getTableName($uid);
        $sql = "INSERT INTO $tbname(uid, name, id) VALUES(:uid, 'a', 100),(:uid, 'b', 100),(:uid, 'c', 100)";
        
        $db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];
        
        $wdb->query($sql, array('uid' => $uid));
    }
}