<?php

class Hapyfish2_Island_Event_Dal_Fans
{
    protected static $_instance;

    protected $tbName = 'island_fans_list';

    /**
     * Single Instance
     *
     * @return Hapyfish2_Island_Dal_Task
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function checkFans($uid)
    {
    	$sql = "SELECT uid FROM $this->tbName WHERE uid=:uid";

        $db = Hapyfish2_Db_Factory::getEventDB('db_0');
        $rdb = $db['r'];

        return $rdb->fetchOne($sql, array('uid' => $uid));
    }

    public function getHasFans()
    {
    	$sql = " SELECT * FROM $this->tbName ";

    	$db = Hapyfish2_Db_Factory::getEventDB('db_0');
    	$rdb = $db['r'];

    	return $rdb->fetchAll($sql);
    }

    public function addFans($data)
    {
        $db = Hapyfish2_Db_Factory::getEventDB('db_0');
        $wdb = $db['w'];

        $wdb->insert($this->tbName, $data);
    }

    public function insertFan($aryUid)
    {
        $db = Hapyfish2_Db_Factory::getEventDB('db_0');
        $wdb = $db['w'];
        $puids = implode(',', $aryUid);
        $sql = "INSERT IGNORE INTO island_fans_list VALUES $puids;";
        return $wdb->query($sql);
    }

}