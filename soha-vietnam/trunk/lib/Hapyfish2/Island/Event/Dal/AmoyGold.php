<?php

class Hapyfish2_Island_Event_Dal_AmoyGold
{
    protected static $_instance;

    protected $tbName = 'island_amoygold_list';

    /**
     * Single Instance
     *
     * @return Hapyfish2_Island_Dal_Task
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    public function getGiftInit($id)
    {
    	$sql = "SELECT * FROM $this->tbName WHERE id=:id ORDER BY order_id ASC;";
    	
    	$db = Hapyfish2_Db_Factory::getEventDB('db_0');
        $rdb = $db['r'];

    	return $rdb->fetchAll($sql, array('id' => $id));
    }
}