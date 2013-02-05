<?php

class Hapyfish2_Island_Dal_Clearcache
{
    protected static $_instance;

    /**
     * Single Instance
     *
     * @return Hapyfish2_Island_Dal_Clearcache
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function getUserTableName($tableId)
    {
        return 'island_user_info_' . $tableId;
    }

    public function getPlantTableName($uid)
    {
        $id = floor($uid/DATABASE_NODE_NUM) % 50;
        return 'island_user_plant_' . $id;
    }
    
    public function getBuildingTableName($uid)
    {
        $id = floor($uid/DATABASE_NODE_NUM) % 50;
        return 'island_user_building_' . $id;
    }
    
    public function getUserPlantList($uid)
    {
        $tbname = $this->getPlantTableName($uid);
        $sql = "SELECT id FROM $tbname WHERE uid=:uid";

        $db = Hapyfish2_Db_Factory::getDB($uid);
        $rdb = $db['r'];

        return $rdb->fetchAll($sql, array('uid' => $uid));
    }
    
    public function getUserBuildingList($uid)
    {
        $tbname = $this->getBuildingTableName($uid);
        $sql = "SELECT id FROM $tbname WHERE uid=:uid";

        $db = Hapyfish2_Db_Factory::getDB($uid);
        $rdb = $db['r'];

        return $rdb->fetchAll($sql, array('uid' => $uid));
    }
    
    public function getUidListByPage($pageIndex, $pageSize, $dbId, $tableId)
    {
    	$tbname = $this->getUserTableName($tableId);
        $start = ($pageIndex-1)*$pageSize;
        
        $sql = "SELECT uid FROM $tbname LIMIT $start,$pageSize";
        
        $db = Hapyfish2_Db_FactoryTool::getDB($dbId);
        $rdb = $db['r'];
        return $rdb->fetchAll($sql);
    }
    
}