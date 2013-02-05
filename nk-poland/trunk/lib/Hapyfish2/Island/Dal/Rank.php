<?php
class Hapyfish2_Island_Dal_Rank
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
    
    protected function getDB()
    {
    	$key = 'db_0';
    	return Hapyfish2_Db_Factory::getBasicDB($key);
    }
    
    public function getUserRankTableName($uid)
    {
    	$id = floor($uid/DATABASE_NODE_NUM) % 10;
    	return 'island_user_rank_' . $id;
    }
    
    public function getCoinRankTableName($uid)
    {
    	$id = floor($uid/DATABASE_NODE_NUM) % 10;
    	return 'island_user_getcoin_' . $id;
    }
    
    public function getInviteTableName($uid)
    {
    	$id = floor($uid/DATABASE_NODE_NUM) % 10;
    	return 'island_user_invitelog_' . $id;
    }
    
    public function getGoldTableName($uid,$time){
        $yearmonth = date('Ym',$time);
        $id = floor($uid/DATABASE_NODE_NUM) % 10;
    	return 'island_user_goldlog_' . $yearmonth . '_' . $id;
    }
    
    public function getUserPayTb($uid)
    {
    	$id = floor($uid/DATABASE_NODE_NUM) % 10;
    	return 'island_user_paylog_' . $id;
    }
    
    public function getCoinLog($id)
    {
   		$tbname = $this->getCoinRankTableName($id);
    	$sql = "SELECT * FROM $tbname ORDER BY num DESC ";
    	$db = Hapyfish2_Db_Factory::getDB($id);
        $rdb = $db['r'];
        return $rdb->fetchAll($sql);
    }
    
    public function getUserGetCoin($uid)
    {
    	$tbname = $this->getCoinRankTableName($uid);
    	$sql = "SELECT * from $tbname where uid=:uid";
    	$db = Hapyfish2_Db_Factory::getDB($uid);
        $rdb = $db['r'];
        return $rdb->fetchRow($sql, array('uid' => $uid));
    }
    
    public function getInviteLog($id ,$strat, $end)
    {
    	$tbname = $this->getInviteTableName($id);
    	$sql = "SELECT uid, count(fid) as num FROM  $tbname where `time`>=:start and `time`<=:end  GROUP BY uid ";
    	$db = Hapyfish2_Db_Factory::getDB($id);
    	$rdb = $db['r'];
    	return $rdb->fetchAll($sql, array('start' => $strat, 'end' => $end));
    }
    
    public function getGoldLog($id, $start, $end, $table)
    {
    	$db = Hapyfish2_Db_Factory::getDB($id);
        $rdb = $db['r'];
    	$tbname = $this->getGoldTableName($id,$table);
    	$sql = "SELECT uid, sum(cost) as num FROM $tbname where `create_time`>=:start and `create_time`<=:end GROUP BY uid";
    	$totalgold = $rdb->fetchAll($sql, array('start' => $start,'end' => $end));
        return $totalgold;
    }
    
    public function getBasicRank($type, $date, $limit)
    {
    	$tbname = 'island_rank';
    	$sql = "SELECT * FROM  $tbname where `date`=:date and `type`=:type ORDER BY num DESC";
    	if($limit>0){
			$sql .=' LIMIT ' .$limit;
    	}
    	$db = $this->getDB();
    	$rdb = $db['r'];
    	return $rdb->fetchAll($sql, array('type' => $type, 'date' => $date));
    }
    
    public function updateUserCoinId($uid,$info)
    {
        $tbname = $this->getCoinRankTableName($uid);
        $db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];
    	$where = $wdb->quoteinto('uid = ?', $uid);
        $wdb->update($tbname, $info, $where);
    }
    
    public function insertUserCoinId($uid,$info)
    {
    	$tbname = $this->getCoinRankTableName($uid);
        $db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];
    	return $wdb->insert($tbname, $info); 
    }
    
    public function clearUserCoin($id, $date)
    {
    	$tbname = $this->getCoinRankTableName($id);
    	$sql = "DELETE FROM $tbname where `date`=$date";
        $db = Hapyfish2_Db_Factory::getDB($id);
        $wdb = $db['w'];
        $wdb->query($sql);
    }
    
    public function getAllRank($date, $type)
    {
    	$tbname = 'island_rank';
    	$sql = "SELECT * FROM $tbname where `date`=:date and `type`=:type";
    	$db = $this->getDB();
    	$rdb = $db['r'];
    	return $rdb->fetchAll($sql, array( 'date'=>$date, 'type' => $type));
    }
    
    public function deleteRankDate($date, $type)
    {
    	$tbname = 'island_rank';
    	$sql = "DELETE FROM $tbname WHERE `date`=:date and `type`=:type";
    	$db = $this->getDB();
    	$wdb = $db['w'];
    	$wdb->query($sql, array('date' => $date, 'type' => $type));
    }
    
    public function getUserRankInfo($uid)
    {
    	$tbname = $this->getUserRankTableName($uid);
    	$sql = "SELECT * FROM $tbname where uid=:uid";
    	$db = Hapyfish2_Db_Factory::getDB($uid);
        $rdb = $db['r'];
        return $rdb->fetchAll($sql, array('uid' => $uid));
    }
    
    public function updateRankNewToOld($type)
    {
    	$tbname = 'island_rank';
    	$sql = "UPDATE $tbname SET `date`=1  WHERE `date`=2 AND `type`=:type";
    	$db = $this->getDB();
    	$wdb = $db['w'];
    	return $wdb->query($sql, array('type' => $type));
    }
    public function insertUserRank($id, $info)
    {
    	$tbname = $this->getUserRankTableName($id);
    	$db = Hapyfish2_Db_Factory::getDB($id);
        $wdb = $db['w'];
    	return $wdb->insert($tbname, $info);
    }
    
    public function clearUserRank($id, $type)
    {
    	$tbname = $this->getUserRankTableName($id);
    	$sql = "DELETE FROM $tbname WHERE `type`=:type";
    	$db = Hapyfish2_Db_Factory::getDB($id);
        $wdb = $db['w'];
        return $wdb->query($sql, array('type' => $type));
    }
    public function getUserRankLimit($id, $type, $limit, $date)
    {
    	if($type == 2){
    		$tbname = $this->getCoinRankTableName($id);
    		$sql = "SELECT uid, num FROM $tbname where `date`=:date";
	    	if($limit > 0){
		    	$sql .= ' LIMIT '. $limit; 
	    	}
	    	$db = Hapyfish2_Db_Factory::getDB($id);
	    	$rdb = $db['r'];
	    	return $rdb->fetchAll($sql, array('date' => $date));
    	} else {
			$tbname = $this->getUserRankTableName($id);
	    	$sql = "SELECT * FROM $tbname WHERE `type`=:type";
	    	if($limit > 0){
	    		$sql .= ' LIMIT '. $limit; 
	    	}
	    	$db = Hapyfish2_Db_Factory::getDB($id);
	    	$rdb = $db['r'];
	    	return $rdb->fetchAll($sql, array('type' => $type));
    	}
    }
    
    public function insertBasicRank($info)
    {
    	$tbname = 'island_rank';
    	$db = $this->getDB();
        $wdb = $db['w'];
    	return $wdb->insert($tbname, $info);
    }
    
    public function updateUserCoin($uid, $date, $num)
    {
    	$tbname = $this->getCoinRankTableName($uid);
    	$sql = "INSERT INTO $tbname VALUES($uid, $num, $date) ON DUPLICATE KEY UPDATE num=:num";
        $db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];
       return $wdb->query($sql, array('num' => $num));
    }
    public function getUserRankCoin($uid, $date)
    {
    	$tbname = $this->getCoinRankTableName($uid);
    	$sql = "SELECT num FROM $tbname where uid=:uid and `date`=:date";
    	$db = Hapyfish2_Db_Factory::getDB($uid);
        $rdb = $db['r'];
    	return $rdb->fetchAll($sql, array( 'uid' => $uid, 'date' => $date));
    }
    public function updateRank($uid, $date, $type, $change)
    {
    	$tbname = 'island_rank';
    	$db = $this->getDB();
    	$wdb = $db['w'];
    	$change = $wdb->quote($change);
    	$sql = "update $tbname set `change`=$change where uid=$uid and `date`=$date and `type`=$type";
    	$wdb->query($sql);
    }
    public function getuid()
    {
    	$tbname = 'island_rank';
    	$db = $this->getDB();
    	$sql = "select DISTINCT(uid) from $tbname";
    	$rdb = $db['r'];
    	return $rdb->fetchCol($sql);
    }
    
    public function getUserPayLog($uid, $start, $end)
    {
    	$tbname = $this->getUserPayTb($uid);
    	$db = Hapyfish2_Db_Factory::getDB($uid);
    	$rdb = $db['r'];
    	$sql = "SELECT sum(gold) as num from $tbname where uid=:uid and create_time>=:start and  create_time<=:end";
    	return $rdb->fetchOne($sql);
    }
    
    public function getUserAllPay($id, $start, $end)
    {
    	$tbname = $this->getUserPayTb($id);
    	$db = Hapyfish2_Db_Factory::getDB($id);
    	$rdb = $db['r'];
    	$sql = "SELECT uid, sum(gold) as num from $tbname where create_time>=:start and  create_time<=:end group by uid";
    	return $rdb->fetchAll($sql);
    }
}