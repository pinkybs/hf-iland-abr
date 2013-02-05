<?php


class Hapyfish2_Island_Dal_GiftPackage
{
    protected static $_instance;

    /**
     * Single Instance
     *
     * @return Hapyfish2_Island_Dal_GiftPackage
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
    	$id = floor($uid/DATABASE_NODE_NUM) % 10;
    	return 'island_user_gift_package_' . $id;
    }

    public function getList($uid)
    {
        $tbname = $this->getTableName($uid);
    	$sql = "SELECT pid,from_uid,gift_type,send_time FROM $tbname WHERE to_uid=:uid";

        $db = Hapyfish2_Db_Factory::getDB($uid);
        $rdb = $db['r'];

        return $rdb->fetchAll($sql, array('uid' => $uid));
    }

    public function getOne($uid, $pid)
    {
        $tbname = $this->getTableName($uid);
    	$sql = "SELECT pid,from_uid,gift_type,send_time,coin,gold,exp,starfish,item_data FROM $tbname WHERE to_uid=:uid AND pid=:pid";

        $db = Hapyfish2_Db_Factory::getDB($uid);
        $rdb = $db['r'];

        return $rdb->fetchRow($sql, array('uid' => $uid, 'pid' => $pid));
    }

    public function getNum($uid)
    {
    	$tbname = $this->getTableName($uid);
    	$sql = "SELECT COUNT(to_uid) FROM $tbname WHERE to_uid=:uid";

        $db = Hapyfish2_Db_Factory::getDB($uid);
        $rdb = $db['r'];

        return $rdb->fetchOne($sql, array('uid' => $uid));
    }

    public function insert($uid, $package)
    {
        $tbname = $this->getTableName($uid);

        $db = Hapyfish2_Db_Factory::getDB($uid);

        $wdb = $db['w'];

    	return $wdb->insert($tbname, $package);
    }

    public function delete($uid, $pid)
    {
        $tbname = $this->getTableName($uid);

        $sql = "DELETE FROM $tbname WHERE to_uid=:uid AND pid=:pid";

        $db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];

        $wdb->query($sql, array('uid' => $uid, 'pid' => $pid));
    }

    public function clear($uid)
    {
        $tbname = $this->getTableName($uid);

        $sql = "DELETE FROM $tbname WHERE to_uid=:uid";

        $db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];

        $wdb->query($sql, array('uid' => $uid));
    }

	/**
	 * select gift id
	 *
	 * @param integer uid
	 * return gift id/null
	 */
	public function getGiftId($uid,$pack)
	{

		$tbName = $this->getTableName($uid);

		$sql = "SELECT pid FROM $tbName WHERE to_uid=:to_uid AND coin=:coin AND item_data=:item_data";

		$db = Hapyfish2_Db_Factory::getDB($uid);
        $rdb = $db['r'];

		return $rdb->fetchOne($sql, array('to_uid' => $pack['to_uid'], 'coin' => $pack['coin'], 'item_data' => $pack['item_data']));
	}

}