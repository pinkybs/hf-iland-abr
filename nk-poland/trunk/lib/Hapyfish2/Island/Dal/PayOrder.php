<?php


class Hapyfish2_Island_Dal_PayOrder
{
    protected static $_instance;

    /**
     * Single Instance
     *
     * @return Hapyfish2_Island_Dal_PayOrder
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function getPayOrderTableName($uid)
    {
    	return 'island_user_payorder';
    }

    public function getOrder($uid, $orderid)
    {
        $tbname = $this->getPayOrderTableName($uid);
        $db = Hapyfish2_Db_Factory::getDB($uid);
        $rdb = $db['r'];

        $sql = "SELECT * FROM $tbname WHERE orderid=:orderid AND uid=:uid";
        return $rdb->fetchRow($sql, array('orderid' => $orderid, 'uid' => $uid));
    }

    public function regOrder($uid, $info)
    {
        $tbname = $this->getPayOrderTableName($uid);
        $db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];
    	return $wdb->insert($tbname, $info);
    }

    public function completeOrder($uid, $orderid, $info)
    {
        $tbname = $this->getPayOrderTableName($uid);
        $db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];
    	$where = $wdb->quoteinto('orderid = ?', $orderid);

        return $wdb->update($tbname, $info, $where);
    }

    public function listOrder($uid, $pageIndex=1, $pageSize=10)
    {
    	$start = ($pageIndex - 1) * $pageSize;
    	$tbname = $this->getPayOrderTableName($uid);
    	$db = Hapyfish2_Db_Factory::getDB($uid);
        $rdb = $db['r'];
        //'2011-05-20'
        $sql = "SELECT * FROM $tbname WHERE uid=:uid AND order_time>1305820800 ORDER BY order_time DESC LIMIT $start,$pageSize";
        return $rdb->fetchAll($sql, array('uid' => $uid));
    }

}