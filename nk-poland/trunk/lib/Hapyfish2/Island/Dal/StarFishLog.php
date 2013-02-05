<?php


class Hapyfish2_Island_Dal_StarFishLog
{
    protected static $_instance;

    /**
     * Single Instance
     *
     * @return Hapyfish2_Island_Dal_StarFishLog
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    public function getStarFishTableName($uid, $yearmonth)
    {
    	$id = floor($uid/DATABASE_NODE_NUM) % 10;
    	return 'island_user_starfishlog_' . $yearmonth . '_' . $id;
    }
    
    public function getStarFish($uid, $yearmonth, $limit = 50)
    {
    	$tbname = $this->getStarFishTableName($uid, $yearmonth);
    	$sql = "SELECT `change`,remain,summary,create_time FROM $tbname WHERE uid=:uid ORDER BY create_time DESC";
    	if ($limit > 0) {
    		$sql .= ' LIMIT ' . $limit;
    	}
    	
        $db = Hapyfish2_Db_Factory::getDB($uid);
        $rdb = $db['r'];
    	
        return $rdb->fetchAll($sql, array('uid' => $uid));
    }
    
    public function insert($uid, $info)
    {
        $yearmonth = date('Ym', $info['create_time']);
    	$tbname = $this->getStarFishTableName($uid, $yearmonth);

        $db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];
        
    	return $wdb->insert($tbname, $info); 	
    }
    
    public function clear($uid, $yearmonth)
    {
        $tbname = $this->getStarFishTableName($uid, $yearmonth);
        
        $sql = "DELETE FROM $tbname WHERE uid=:uid";
        
        $db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];
        
        $wdb->query($sql, array('uid' => $uid));
    }
}