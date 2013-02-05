<?php

/**
 * Event NewDays
 *
 * @package    Island/Event/Dal
 * @copyright  Copyright (c) 2011 Happyfish Inc.
 * @create     2011/12/23    zhangli
*/
class Hapyfish2_Island_Event_Dal_NewDays
{
    protected static $_instance;

    protected $table_event_newdays_items = 'island_event_newdays_items';

    /**
     * Single Instance
     *
     * @return Hapyfish2_Island_Dal_NewDays
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
	
    //获取3种锤子砸卡片的信息
    public function getListArr()
    {
    	$sql = "SELECT eid,item_odds FROM $this->table_event_newdays_items";
    	
		$db = Hapyfish2_Db_Factory::getEventDB('db_0');
        $rdb = $db['r'];
        
        return $rdb->fetchAll($sql);
    }
    
}