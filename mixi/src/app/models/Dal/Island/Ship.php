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
class Dal_Island_Ship extends Dal_Abstract
{
    /**
     * table name
     *
     * @var string
     */
    protected $table_ship = 'island_ship';

    protected static $_instance;

    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * insert ship
     *
     * @param array $info
     * @return integer
     */
    public function insertShip($info)
    {
        $this->_wdb->insert($this->table_ship, $info);
        return $this->_wdb->lastInsertId();
    }

    /**
     * get ship info by ship id
     *
     * @param integer $id
     * @return array
     */
    public function getShip($id)
    {
        $sql = "SELECT * FROM $this->table_ship WHERE sid=:id ";

        return $this->_rdb->fetchRow($sql,array('id'=>$id));
    }
            
	/**
	 * get ship list
	 * return array
	 */
    public function getShipList()
    {
        $sql = "SELECT sid AS boatId,sid AS level,name,class_name AS className,start_visitor_num AS startVisitorNum, 
                safe_visitor_num AS safeVisitorNum,wait_time AS waitTime,safe_time_1 AS safeTime1,safe_time_2 AS safeTime2, 
                coin,gem,level AS needLevel FROM $this->table_ship";
        return $this->_rdb->fetchAll($sql);
    }
    
}