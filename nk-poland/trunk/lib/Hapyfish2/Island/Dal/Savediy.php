<?php


class Hapyfish2_Island_Dal_Savediy
{
    protected static $_instance;

    /**
     * Single Instance
     *
     * @return Hapyfish2_Island_Dal_Savediy
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function getUidListByPage($dbId, $tableId, $startTime)
    {
        $db = Hapyfish2_Db_FactoryTool::getDB($dbId);
        $rdb = $db['r'];
    	
    	$tbname = 'island_user_info_' . $tableId;
        $sql = "SELECT uid FROM $tbname WHERE last_login_time > $startTime ";
        
        return $rdb->fetchAll($sql);
    }
    
}