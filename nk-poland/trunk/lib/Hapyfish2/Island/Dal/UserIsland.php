<?php


class Hapyfish2_Island_Dal_UserIsland
{
    protected static $_instance;

    /**
     * Single Instance
     *
     * @return Hapyfish2_Island_Dal_UserIsland
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
    	$id = floor($uid/DATABASE_NODE_NUM) % 10;
    	return 'island_user_island_info_' . $id;
    }
        
    public function get($uid)
    {
        $tbname = $this->getTableName($uid);
    	$sql = "SELECT praise,position_count,bg_island,bg_island_id,bg_sky,bg_sky_id,bg_sea,bg_sea_id,bg_dock,bg_dock_id, 
		    	bg_island_2,bg_island_id_2,bg_sky_2,bg_sky_id_2,bg_sea_2,bg_sea_id_2,bg_dock_2,bg_dock_id_2, 
		    	bg_island_3,bg_island_id_3,bg_sky_3,bg_sky_id_3,bg_sea_3,bg_sea_id_3,bg_dock_3,bg_dock_id_3, 
		    	bg_island_4,bg_island_id_4,bg_sky_4,bg_sky_id_4,bg_sea_4,bg_sea_id_4,bg_dock_4,bg_dock_id_4,
		    	current_island,unlock_island,praise_2,praise_3,praise_4 
		    	FROM $tbname WHERE uid=:uid";
    	
        $db = Hapyfish2_Db_Factory::getDB($uid);
        $rdb = $db['r'];
    	
        return $rdb->fetchRow($sql, array('uid' => $uid), Zend_Db::FETCH_NUM);
    }
    
    public function update($uid, $info)
    {
        $tbname = $this->getTableName($uid);
        
        $db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];
        
    	$where = $wdb->quoteinto('uid = ?', $uid);
    	
        $wdb->update($tbname, $info, $where);   	
    }
    
    public function init($uid)
    {
        $tbname = $this->getTableName($uid);
        
        $sql = "INSERT INTO $tbname(uid,praise,position_count,current_island,bg_island,bg_island_id,bg_sky,bg_sky_id,bg_sea,bg_sea_id,bg_dock,bg_dock_id) 
        	    VALUES(:uid, 57, 3, 1, 27211, 1, 24112, 2, 22913, 3, 99714, 4)";
        
        $db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];
        
        $wdb->query($sql, array('uid' => $uid));
    }

    public function delete($uid)
    {
        $tbname = $this->getTableName($uid);
        
        $sql = "DELETE FROM $tbname WHERE uid=:uid";
        
        $db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];
        
        $wdb->query($sql, array('uid' => $uid));
    }
}