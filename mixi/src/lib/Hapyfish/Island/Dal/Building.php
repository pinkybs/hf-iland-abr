<?php

class Hapyfish_Island_Dal_Building extends Hapyfish_Island_Dal_Abstract
{
	
	protected $table_user_building = 'island_user_building';
	
	protected $table_island_building = 'island_island_building';

    protected static $_instance;

    /**
     * Single Instance
     *
     * @return Hapyfish_Island_Dal_Building
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function getTableName($uid, $tbname = null)
    {
        if ( $tbname == null ) {
            $tbname = $this->table_user_building;
        }
        $n = $uid % 10;
        return $tbname . '_' . $n;
    }
    
    /**
     * get user using building
     *
     * @param integer $uid
     * @return array
     */
    public function getUsingBuilding($uid)
    {
        $tbName = $this->getTableName($uid);
        $n = $uid % 10;
        $sql = "SELECT concat(id,$n,item_type) AS id,bid AS cid,x,y,z,mirro FROM $tbName WHERE status=1 AND uid=:uid";
        
        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }
    
    /**
     * get building info by id
     *
     * @param integer $bid
     * @return array
     */
    public function getBuildingById($bid)
    {
        $sql = "SELECT * FROM $this->table_island_building WHERE bid=:bid";
        return $this->_rdb->fetchRow($sql, array('bid' => $bid));
    }
    
}