<?php
/**
 * lei.wu,
 * lei.wu@hapyfish.com,
 * 2011-4-29
 * */
class Hapyfish2_Island_Dal_Robot
{
	protected static $_instance;
	
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
    
 	public function getTableName($uid)
    {
    	$id = floor($uid/4) % 10;
    	return 'island_user_robot_friend_' . $id;
    }
    
    public function getRobotFriend($uid)
    {
    	$tbname = $this->getTableName($uid);
    	$sql = "select fid from $tbname where uid=:uid";
    	$db = Hapyfish2_Db_Factory::getDB($uid);
        $rdb = $db['r'];
        return $rdb->fetchone($sql, array('uid' => $uid));
    }
    
    public function addRobot($uid, $fid)
    {
    	$tbname = $this->getTableName($uid);
    	$sql = "INSERT INTO $tbname(uid, fid) VALUES(:uid, :fid)";
        $db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];
        $wdb->query($sql, array('uid' => $uid, 'fid' => $fid));
    	
    }
    
    public function clearrobot($uid)
    {
    	$tbname = $this->getTableName($uid);
    	$sql = "DELETE FROM $tbname where uid=$uid";
    	$db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];
        $wdb->query($sql);
    }
}