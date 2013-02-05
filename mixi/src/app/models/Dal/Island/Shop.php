<?php

require_once 'Dal/Abstract.php';

/**
 * Island datebase's Operation
 *
 *
 * @package    Dal
 * @copyright  Copyright (c) 2008 Community Factory Inc. (http://communityfactory.com)
 * @create      2010/01/20    Liz
 */
class Dal_Island_Shop extends Dal_Abstract
{
    protected static $_instance;
    
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
        $sql = "SELECT cid FROM island_card WHERE can_buy = 1 ";

        return $this->_rdb->fetchAll($sql);
    }

    /**
     * get island background list
     *
     * @return array
     */
    public function getBackgroundList()
    {
        $sql = "SELECT bgid AS cid FROM island_island_background WHERE can_buy = 1 ";

        return $this->_rdb->fetchAll($sql);
    }
    
    /**
     * get island building list
     *
     * @return array
     */
    public function getBuildingList()
    {
        $sql = "SELECT bid AS cid FROM island_island_building WHERE can_buy = 1 ";

        return $this->_rdb->fetchAll($sql);
    }

    /**
     * get island building list
     *
     * @return array
     */
    public function getPlantList()
    {
        $sql = "SELECT bid AS cid FROM island_island_plant WHERE can_buy = 1 ";

        return $this->_rdb->fetchAll($sql);
    }
        
}