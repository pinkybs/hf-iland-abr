<?php

class Hapyfish2_Island_Dal_Statics
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
     * @return Hapyfish2_Island_Dal_Statics
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    public function getUidList()
    {
    	$sql = "SELECT `name`, id FROM seq_uid";
    	
        $db = $this->getDB();
        $rdb = $db['r'];
    	
        return $rdb->fetchPairs($sql);
    }
    
}