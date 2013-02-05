<?php


class Hapyfish2_Island_Dal_Card
{
    protected static $_instance;

    /**
     * Single Instance
     *
     * @return Hapyfish2_Island_Dal_Card
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
    	return 'island_user_card_' . $id;
    }

    public function get($uid)
    {
        $tbname = $this->getTableName($uid);
    	$sql = "SELECT cid,count FROM $tbname WHERE uid=:uid";

        $db = Hapyfish2_Db_Factory::getDB($uid);
        $rdb = $db['r'];

        return $rdb->fetchPairs($sql, array('uid' => $uid));
    }

    public function update($uid, $cid, $count)
    {
        $tbname = $this->getTableName($uid);
        $sql = "INSERT INTO $tbname (uid, cid, count) VALUES($uid, $cid, $count) ON DUPLICATE KEY UPDATE count=$count";

        $db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];

        $wdb->query($sql);
    }

    public function init($uid)
    {
        $tbname = $this->getTableName($uid);

        $sql = "INSERT INTO $tbname(uid, cid, count)
        		VALUES
				(:uid, 26241, 20),
				(:uid, 26341, 2),
				(:uid, 26441, 2),
				(:uid, 26541, 5),
				(:uid, 26641, 5),
				(:uid, 26841, 2),
				(:uid, 27041, 2),
				(:uid, 27141, 2),
				(:uid, 67441, 2),
				(:uid, 67541, 2)";
        $db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];

        $wdb->query($sql, array('uid' => $uid));
    }

    public function clear($uid)
    {
        $tbname = $this->getTableName($uid);
        $sql = "DELETE FROM $tbname WHERE uid=:uid";

        $db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];

        $wdb->query($sql, array('uid' => $uid));
    }

}