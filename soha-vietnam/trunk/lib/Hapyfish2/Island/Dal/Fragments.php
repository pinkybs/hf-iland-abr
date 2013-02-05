<?php
class Hapyfish2_Island_Dal_Fragments
{
    protected static $_instance;

    /**
     * Single Instance
     *
     * @return Hapyfish2_Island_Dal_Rank
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    protected function getEventDB()
    {
    	$key = 'db_0';
    	return Hapyfish2_Db_Factory::getEventDB($key);
    }
    
    public function getUserFragmentsTable($uid)
    {
    	$id = floor($uid/DATABASE_NODE_NUM) % 10;
    	return 'island_user_polish_' . $id;
    }
    
    public function getAwardConfig()
    {
    	$tbname = 'award_config';
    	$sql = "SELECT * FROM $tbname";
    	$db = $this->getEventDB();
    	$rdb= $db['r'];
    	return $rdb->fetchRow($sql);
    	
    }
    
    public function getUserFragments($uid)
    {
    	$tbname = $this->getUserFragmentsTable($uid);
    	$sql = "select * from $tbname where uid=:uid";
    	$db = Hapyfish2_Db_Factory::getDB($uid);
        $rdb = $db['r'];
        return $rdb->fetchRow($sql, array('uid' => $uid));
    }
    
    public function insert($uid, $data)
    {
     	$tbname = $this->getUserFragmentsTable($uid);
    	$db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];
        return $wdb->insert($tbname, $data);
    }
    
    public function update($uid, $info)
    {
        $tbname = $this->getUserFragmentsTable($uid);
        $db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];
    	$where = $wdb->quoteinto('uid = ?', $uid);

        $wdb->update($tbname, $info, $where);
    }
    
}