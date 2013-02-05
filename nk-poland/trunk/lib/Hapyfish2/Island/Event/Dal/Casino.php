<?php


/**
 * Event Casino
 *
 * @package    Island/Event/Dal
 * @copyright  Copyright (c) 2008 Happyfish Inc.
 * @create     2011/05/10    Nick
*/
class Hapyfish2_Island_Event_Dal_Casino
{
    protected static $_instance;

    /**
     * Single Instance
     *
     * @return Hapyfish2_Island_Event_Dal_Casino
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function getPointTableName($uid)
    {
    	$id = floor($uid/DATABASE_NODE_NUM) % 10;
    	return 'island_user_event_point_' . $id;
    }
    
    public function getPointLogTableName($yearmonth)
    {
    	return 'island_user_event_pointlog_' . $yearmonth;
    }
    
    /**
     * get user point
     *
     * @param int uid
     * @return int
     */
    public function getUserPoint($uid)
    {
    	$tbname = $this->getPointTableName($uid);
        $sql = "SELECT point FROM $tbname WHERE uid=:uid ";
        
        $db = Hapyfish2_Db_Factory::getDB($uid);
        $rdb = $db['r'];
        
        return $rdb->fetchOne($sql, array('uid' => $uid));
    }

    /**
     * update user point
     *
     * @param int $uid
     * @param int $point
     * @return void
     */
    public function updateUserPoint($uid, $point)
    {
        $db = Hapyfish2_Db_Factory::getDB($uid);
        $rdb = $db['r'];
        $wdb = $db['w'];
        
    	$tbname = $this->getPointTableName($uid);
    	$sql = "SELECT COUNT(1) FROM $tbname WHERE uid=:uid ";
    	$result = $rdb->fetchOne($sql, array('uid' => $uid));
    	
    	if ( $result == 1 ) {
		    $sql = "UPDATE $tbname SET point = point + :point WHERE uid=:uid ";
		    $wdb->query($sql, array('uid' => $uid, 'point' => $point));
    	}
    	else {
    		$array = array('uid' => $uid, 'point' => $point);
    		$wdb->insert($tbname, $array);
    	}
    }
    
    /**
     * get point change list
     *
     * @return array
     */
    public function getPointChangeList()
    {
    	$tbname = $this->getLogTableName();
        $sql = "SELECT * FROM $tbname ORDER BY create_time DESC LIMIT 0,10 ";
        
        $db = Hapyfish2_Db_Factory::getDB();
        $rdb = $db['r'];
        
        return $rdb->fetchAll($sql);
    }
	
    /**
     * add user point change log
     * 
     */
    public function addUserPointChangeLog($uid, $info)
    {
		$yearmonth = date('Ym', $info['create_time']);
    	$tbname = $this->getPointLogTableName($yearmonth);
        
        $db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];

        $wdb->insert($tbname, $info); 
    }
}