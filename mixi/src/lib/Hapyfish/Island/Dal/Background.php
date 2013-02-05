<?php

class Hapyfish_Island_Dal_Background extends Hapyfish_Island_Dal_Abstract
{
	protected $table_island_background = 'island_island_background';

    protected static $_instance;

    /**
     * Single Instance
     *
     * @return Hapyfish_Island_Dal_Background
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
            $tbname = $this->table_user_plant;
        }
        $n = $uid % 10;
        return $tbname . '_' . $n;
    }
    
    /**
     * get user using background list info
     *
     * @param integer $uid
     * @return array
     */
    public function getUsingBackground($uid)
    {
        $sql = "SELECT * FROM island_user_background WHERE status=1 AND uid=:uid";

        return $this->_rdb->fetchAll($sql, array('uid' => $uid));
    }
    
    /**
     * get background info by id
     *
     * @param integer $bgId
     * @return array
     */
    public function getBackgroundById($bgId)
    {
        $sql = "SELECT * FROM $this->table_island_background WHERE bgid=:bgid";
        return $this->_rdb->fetchRow($sql, array('bgid' => $bgId));
    }
    
}