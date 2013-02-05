<?php
class Hapyfish2_Island_Event_Dal_StarfishSale
{
    protected static $_instance;

    /**
     * Single Instance
     *
     * @return Hapyfish2_Island_Event_Dal_StarfishSale
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function getInviteTable($uid)
    {
    	$id = floor($uid/DATABASE_NODE_NUM) % 10;
    	return 'island_user_invitelog_' . $id;
    }
    public function geteventDB()
    {
    	$key = 'db_0';
    	return Hapyfish2_Db_Factory::getBasicDB($key);
    }

   public function getSaleList(){
        $sql = 'select * from island_starfish_externalmall order by sort';
        $db = $this->geteventDB();
        $rdb = $db['r'];
        return $rdb->fetchAll($sql);
    }
    public function getInviteCount($uid,$start)
    {
        $tbname = $this->getInviteTable($uid);
    	$sql = "SELECT COUNT(fid) AS c FROM $tbname WHERE uid=:uid and `time`>=:start";
        $db = Hapyfish2_Db_Factory::getDB($uid);
        $rdb = $db['r'];
        return $rdb->fetchOne($sql, array('uid' => $uid,'start' => $start));
    
    }
    public function getdetail($cid){
        $sql = 'select * from island_starfish_externalmall where cid=:cid';
        $db = $this->geteventDB();
        $rdb = $db['r'];
        return $rdb->fetchRow($sql,array('cid' => $cid));
    }
    public function insert($info){
        $tbname = 'island_starfish_externalmall';
        $db = $this->geteventDB();
        $wdb = $db['w'];
    	return $wdb->insert($tbname, $info); 
    }
    public function delete($cid){
        $sql = "DELETE FROM island_starfish_externalmall where cid=:cid";
        $db = $this->geteventDB();
        $wdb = $db['w'];
        $wdb->query($sql, array('cid' => $cid));
    }
    public function getRose($u){
    	$sql = "select uid,rose from island_user_event_valentine";
    	$db = Hapyfish2_Db_Factory::getDB($u);
        $rdb = $db['r'];
        return $rdb->fetchAll($sql);
    }
}