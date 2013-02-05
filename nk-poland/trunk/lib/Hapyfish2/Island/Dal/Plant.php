<?php


class Hapyfish2_Island_Dal_Plant
{
    protected static $_instance;

    /**
     * Single Instance
     *
     * @return Hapyfish2_Island_Dal_Plant
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function getTableName($uid)
    {
    	$id = floor($uid/DATABASE_NODE_NUM) % 50;
    	return 'island_user_plant_' . $id;
    }

    public function getAllIds($uid)
    {
        $tbname = $this->getTableName($uid);
    	$sql = "SELECT id FROM $tbname WHERE uid=:uid";

        $db = Hapyfish2_Db_Factory::getDB($uid);
        $rdb = $db['r'];

        return $rdb->fetchCol($sql, array('uid' => $uid));
    }

    public function getOnIslandIds($uid, $islandId)
    {
        $tbname = $this->getTableName($uid);
    	$sql = "SELECT id FROM $tbname WHERE uid=:uid AND status=:islandId";

        $db = Hapyfish2_Db_Factory::getDB($uid);
        $rdb = $db['r'];

        return $rdb->fetchCol($sql, array('uid' => $uid, 'islandId' => $islandId));
    }

    public function getAll($uid)
    {
        $tbname = $this->getTableName($uid);
    	$sql = "SELECT id,cid,level,item_id,item_type,x,y,z,mirro,can_find,start_pay_time,wait_visitor_num,delay_time,event,start_deposit,deposit,status FROM $tbname WHERE uid=:uid";

        $db = Hapyfish2_Db_Factory::getDB($uid);
        $rdb = $db['r'];

        return $rdb->fetchAll($sql, array('uid' => $uid), Zend_Db::FETCH_NUM);
    }

    public function getOnIsland($uid, $islandId)
    {
        $tbname = $this->getTableName($uid);
    	$sql = "SELECT id,cid,level,item_id,item_type,x,y,z,mirro,can_find,start_pay_time,wait_visitor_num,delay_time,event,start_deposit,deposit,status FROM $tbname WHERE uid=:uid AND status=:islandId";

        $db = Hapyfish2_Db_Factory::getDB($uid);
        $rdb = $db['r'];

        return $rdb->fetchAll($sql, array('uid' => $uid, 'islandId' => $islandId), Zend_Db::FETCH_NUM);
    }

    public function getOneOnIsland($uid, $id, $userCurrentIsland)
    {
        $tbname = $this->getTableName($uid);
    	$sql = "SELECT id,cid,level,item_id,item_type,x,y,z,mirro,can_find,start_pay_time,wait_visitor_num,delay_time,event,start_deposit,deposit,status FROM $tbname WHERE id=:id AND uid=:uid AND status=:islandId";

        $db = Hapyfish2_Db_Factory::getDB($uid);
        $rdb = $db['r'];

        return $rdb->fetchRow($sql, array('id' => $id, 'uid' => $uid, 'islandId' => $userCurrentIsland), Zend_Db::FETCH_NUM);
    }

    public function getOne($uid, $id)
    {
        $tbname = $this->getTableName($uid);
    	$sql = "SELECT id,cid,level,item_id,item_type,x,y,z,mirro,can_find,start_pay_time,wait_visitor_num,delay_time,event,start_deposit,deposit,status FROM $tbname WHERE id=:id AND uid=:uid";

        $db = Hapyfish2_Db_Factory::getDB($uid);
        $rdb = $db['r'];

        return $rdb->fetchRow($sql, array('id' => $id, 'uid' => $uid), Zend_Db::FETCH_NUM);
    }

    public function getMultiOnIsland($uid, $ids)
    {
        $tbname = $this->getTableName($uid);
    	$sql = "SELECT id,cid,level,item_id,item_type,x,y,z,mirro,can_find,pay_time,ticket,start_pay_time,wait_visitor_num,delay_time,event,start_deposit,deposit,status FROM $tbname WHERE uid=:uid AND status=1 AND id in ($ids)";

        $db = Hapyfish2_Db_Factory::getDB($uid);
        $rdb = $db['r'];

        return $rdb->fetchAll($sql, array('uid' => $uid), Zend_Db::FETCH_NUM);
    }

    public function getInWareHouse($uid)
    {
        $tbname = $this->getTableName($uid);
    	$sql = "SELECT id,cid,level,item_id,item_type,x,y,z,mirro,can_find,start_pay_time,wait_visitor_num,delay_time,event,start_deposit,deposit,status FROM $tbname WHERE uid=:uid AND status=0";

        $db = Hapyfish2_Db_Factory::getDB($uid);
        $rdb = $db['r'];

        return $rdb->fetchAll($sql, array('uid' => $uid), Zend_Db::FETCH_NUM);
    }

    public function getTopLevelGroupByItem($uid)
    {
        $tbname = $this->getTableName($uid);
    	$sql = "SELECT item_id,level FROM (SELECT item_id,level FROM $tbname WHERE uid=:uid ORDER BY level DESC) AS c GROUP BY item_id";

        $db = Hapyfish2_Db_Factory::getDB($uid);
        $rdb = $db['r'];

        return $rdb->fetchPairs($sql, array('uid' => $uid));
    }

    public function getAllByItemKind($uid)
    {
        $tbname = $this->getTableName($uid);
    	$sql = "SELECT item_id,level FROM $tbname WHERE uid=:uid ORDER BY level DESC";

        $db = Hapyfish2_Db_Factory::getDB($uid);
        $rdb = $db['r'];

        return $rdb->fetchAll($sql, array('uid' => $uid), Zend_Db::FETCH_NUM);
    }

    public function insert($uid, $plant)
    {
        $tbname = $this->getTableName($uid);

        $db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];

    	$wdb->insert($tbname, $plant);
        return $wdb->lastInsertId();
    }

    public function update($uid, $id, $info)
    {
        $tbname = $this->getTableName($uid);

        $db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];

    	$uid = $wdb->quote($uid);
        $id = $wdb->quote($id);
    	$where = "uid=$uid AND id=$id";

        $wdb->update($tbname, $info, $where);
    }

    public function delete($uid, $id)
    {
        $tbname = $this->getTableName($uid);

        $sql = "DELETE FROM $tbname WHERE uid=:uid AND id=:id";

        $db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];

        $wdb->query($sql, array('uid' => $uid, 'id' => $id));
    }

    public function init($uid)
    {
        $time = time();
        $payTime = $time - 3600;
        $payTime2 = $time - 3420;
    	$tbname = $this->getTableName($uid);

        $sql = "INSERT INTO $tbname(uid,id,cid,level,item_id,item_type,x,y,z,mirro,status,buy_time,start_pay_time,start_deposit,deposit)
             VALUES
             (:uid, 1, 732, 2, 6,   32, 6, 12, 0, 0, 1, $time, $payTime, 300, 300),
             (:uid, 2, 21332, 2, 212, 32, 3, 8, 0, 0, 1, $time, $payTime2, 100, 100),
             (:uid, 3, 632, 2, 1, 32, 7, 7, 0, 0, 1, $time, 0, 0, 0),
             (:uid, 4, 99431, 5, 994, 31, 11, 2, 0, 0, 1, $time, 0, 0, 0),
             (:uid, 5, 99531, 5, 995, 31, 11, 9, 0, 0, 1, $time, 0, 0, 0),
             (:uid, 6, 99631, 5, 996, 31, 7, 2, 0, 0, 1, $time, 0, 0, 0)";

        $db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];

        $wdb->query($sql, array('uid' => $uid));
    }

    public function upgradeCoordinate($uid, $islandId, $step = 1)
    {
    	$tbname = $this->getTableName($uid);
    	$sql = "UPDATE $tbname SET x=x+$step,y=y+$step WHERE uid=:uid AND status=:islandId";

    	$db = Hapyfish2_Db_Factory::getDB($uid);
    	$wdb = $db['w'];
    	return $wdb->query($sql, array('uid' => $uid, 'islandId' => $islandId));
    }

    public function clear($uid)
    {
        $tbname = $this->getTableName($uid);

        $sql = "DELETE FROM $tbname WHERE uid=:uid";

        $db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];

        $wdb->query($sql, array('uid' => $uid));
    }

    public function initNewIsland($uid, $islandId)
    {
        $time = time();
        $payTime = $time - 3600;
        $payTime2 = $time - 3420;
    	$tbname = $this->getTableName($uid);

    	if ( $islandId == 2 ) {
        	$sql = "INSERT INTO $tbname(uid,id,cid,level,item_id,item_type,x,y,z,mirro,status,buy_time,start_pay_time,start_deposit,deposit)
             VALUES
             (:uid, 11, 632,   1, 6,   32, 6, 7, 0, 0, 2, $time, 0, 0, 0),
             (:uid, 12, 21232, 1, 212, 32, 4, 1, 0, 0, 2, $time, 0, 0, 0),
             (:uid, 13, 87031, 3, 870, 31, 5, 4, 0, 0, 2, $time, 0, 0, 0)";
    	}
    	else if ( $islandId == 3 ) {
        	$sql = "INSERT INTO $tbname(uid,id,cid,level,item_id,item_type,x,y,z,mirro,status,buy_time,start_pay_time,start_deposit,deposit)
             VALUES
             (:uid, 1, 632,   1, 6,   32, 6, 7, 0, 0, 1, $time, $payTime, 300, 300),
             (:uid, 2, 21232, 1, 212, 32, 4, 1, 0, 0, 1, $time, $payTime2, 100, 100)";
    	}
    	else if ( $islandId == 4 ) {
        	$sql = "INSERT INTO $tbname(uid,id,cid,level,item_id,item_type,x,y,z,mirro,status,buy_time,start_pay_time,start_deposit,deposit)
             VALUES
             (:uid, 1, 632,   1, 6,   32, 6, 7, 0, 0, 1, $time, $payTime, 300, 300),
             (:uid, 2, 21232, 1, 212, 32, 4, 1, 0, 0, 1, $time, $payTime2, 100, 100)";
    	}

        $db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];

        $wdb->query($sql, array('uid' => $uid));
    }

    public function clearNewIsland($uid)
    {
        $tbname = $this->getTableName($uid);

        $sql = "DELETE FROM $tbname WHERE uid=:uid AND status>1";

        $db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];

        return $wdb->query($sql, array('uid' => $uid));
    }

    public function clearDiy($uid, $islandId)
    {
        $tbname = $this->getTableName($uid);

        $sql = "UPDATE $tbname SET status=0 WHERE uid=:uid AND status=:islandId";

        $db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];

        return $wdb->query($sql, array('uid' => $uid, 'islandId' => $islandId));
    }

    public function getAllCid($uid)
    {
        $tbname = $this->getTableName($uid);
    	$sql = "SELECT cid FROM $tbname WHERE uid=:uid";

        $db = Hapyfish2_Db_Factory::getDB($uid);
        $rdb = $db['r'];

        return $rdb->fetchAll($sql, array('uid' => $uid));
    }

	public function checkUseing($uid, $cid)
	{
		$tbname = $this->getTableName($uid);

		$sql = " SELECT id,item_type FROM $tbname WHERE uid=:uid AND cid=:cid ";

		$db = Hapyfish2_Db_Factory::getDB($uid);
        $rdb = $db['r'];

        return $rdb->fetchRow($sql, array('uid' => $uid, 'cid' => $cid));
	}

}