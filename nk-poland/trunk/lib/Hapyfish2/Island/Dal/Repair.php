<?php


class Hapyfish2_Island_Dal_Repair
{
    protected static $_instance;

    /**
     * Single Instance
     *
     * @return Hapyfish2_Island_Dal_Repair
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function getUidListByPage($dbId, $tableId, $cid, $plantLevel)
    {
        $db = Hapyfish2_Db_FactoryTool::getDB($dbId);
        $rdb = $db['r'];
    	
    	$tbname = 'island_user_plant_' . $tableId;
        $sql = "SELECT uid FROM $tbname WHERE level<>:level AND cid=:cid GROUP BY uid ";
        
        return $rdb->fetchAll($sql, array('level'=>$plantLevel, 'cid'=>$cid));
    }
    
}