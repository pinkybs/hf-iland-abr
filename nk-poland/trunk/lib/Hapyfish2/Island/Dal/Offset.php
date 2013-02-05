<?php
class Hapyfish2_Island_Dal_Offset
{
    protected static $_instance;

    /**
     * Single Instance
     *
     * @return Hapyfish2_Island_Dal_Rank
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    public function getTableName($uid){
        $yearmonth = date('Ym');
        $id = floor($uid/8) % 10;
    	return 'island_user_coinlog_' . $yearmonth . '_' . $id;
    }
    
    public function getUserCostCoin($id)
    {
    	$tbname = $this->getTableName($id);
    	$summary = '扩建岛屿';
    	$sql = "SELECT uid, sum(cost) as total FROM $tbname where summary='$summary' group by uid";
    	$db = Hapyfish2_Db_Factory::getDB($id);
        $rdb = $db['r'];
    	return $rdb->fetchAll($sql);
    }
    
    public function delUserCoinLog($uid)
    {
    	$tbname = $this->getTableName($uid);
    	$summary = '扩建岛屿';
    	$sql = "DELETE FROM $tbname where uid=$uid and summary='$summary'";
    	$db = Hapyfish2_Db_Factory::getDB($uid);
    	$wdb = $db['w'];
        $wdb->query($sql);
    }
}