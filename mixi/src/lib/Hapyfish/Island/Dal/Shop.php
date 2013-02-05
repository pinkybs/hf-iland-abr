<?php

class Hapyfish_Island_Dal_Shop extends Hapyfish_Island_Dal_Abstract
{
    protected static $_instance;
    
    /**
     * Single Instance
     *
     * @return Hapyfish_Island_Dal_Shop
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }
  
    /**
     * get card list
     *
     * @return array
     */
    public function getCardList()
    {
        $sql = "SELECT cid FROM island_card WHERE can_buy = 1";

        return $this->_rdb->fetchAll($sql);
    }

    /**
     * get island background list
     *
     * @return array
     */
    public function getBackgroundList()
    {
        $sql = "SELECT bgid AS cid FROM island_island_background WHERE can_buy = 1";

        return $this->_rdb->fetchAll($sql);
    }
    
    /**
     * get island building list
     *
     * @return array
     */
    public function getBuildingList()
    {
        $sql = "SELECT bid AS cid FROM island_island_building WHERE can_buy = 1";

        return $this->_rdb->fetchAll($sql);
    }

    /**
     * get island building list
     *
     * @return array
     */
    public function getPlantList()
    {
        $sql = "SELECT bid AS cid FROM island_island_plant WHERE can_buy = 1";

        return $this->_rdb->fetchAll($sql);
    }
    
}