<?php

class Hapyfish2_Island_Dal_LotteryItemOdds
{
    protected static $_instance;

    protected function getDB()
    {
    	$key = 'db_0';
    	return Hapyfish2_Db_Factory::getBasicDB($key);
    }

    /**
     * Single Instance
     *
     * @return Hapyfish2_Island_Dal_Server
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function lstItemOddsByCategory($id)
    {
    	$sql = "SELECT `order`,item_id,item_type,item_num,item_odds FROM island_lottery_item_odds WHERE category_id=:category_id ";

        $db = self::getDB();
        $rdb = $db['r'];

        return $rdb->fetchAll($sql, array('category_id' => $id));
    }
}