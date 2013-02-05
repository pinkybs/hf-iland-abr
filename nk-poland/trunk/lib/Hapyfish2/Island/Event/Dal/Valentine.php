<?php

class Hapyfish2_Island_Event_Dal_Valentine
{
    protected static $_instance;

    /**
     * Single Instance
     *
     * @return Hapyfish2_Island_Event_Dal_Valentine
     */
    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function get($uid)
    {
        $db = Hapyfish2_Db_Factory::getDB($uid);
        $rdb = $db['r'];
		$sql = "SELECT uid,rose,rose_tot1,rose_tot2,rose_tot3,gain_pisces FROM island_user_event_valentine WHERE uid=:uid";
        return $rdb->fetchRow($sql, array('uid' => $uid));
    }

    public function insert($uid, $info)
    {
        $db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];
    	return $wdb->insert('island_user_event_valentine', $info);
    }

    public function delete($uid)
    {
    	$db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];
    	$sql = "DELETE FROM island_user_event_valentine WHERE uid=:uid";
        return $wdb->query($sql, array('uid' => $uid));
    }

    /**
     * update
     *
     * @param integer $uid
     * @param array $info
     * @return void
     */
    public function update($uid, $info)
    {
    	$db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];
        $where = $wdb->quoteinto('uid = ?', $uid);
        return $wdb->update('island_user_event_valentine', $info, $where);
    }

	/**
     * update user Valentine by field name
     *
     * @param integer $uid
     * @param string $field
     * @param integer $change
     * @return void
     */
    public function updateByField($uid, $field, $change)
    {
        $db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];
		$sql = "UPDATE island_user_event_valentine SET $field = $field + :change WHERE uid=:uid ";
        return $wdb->query($sql,array('uid'=>$uid, 'change'=>$change));
    }

	/**
     * update user Valentine by multiple field name
     *
     * @param integer $uid
     * @param array $param
     * @return void
     */
    public function updateByMultipleField($uid, $param)
    {
    	$db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];
        $change = array();
        foreach ( $param as $k => $v ) {
            $change[] = $k . '=' . $k . '+' . $v;
        }
        $s1 = join(',', $change);
        $sql = "UPDATE island_user_event_valentine SET $s1 WHERE uid=:uid ";
        return $wdb->query($sql, array('uid'=>$uid));
    }

    /* exchange log */
	public function getTableName($uid)
    {
    	$id = floor($uid/DATABASE_NODE_NUM) % 10;
    	return 'island_user_event_valentine_exchange_' . $id;
    }

    public function getUserExchange($uid)
    {
    	$db = Hapyfish2_Db_Factory::getDB($uid);
        $rdb = $db['r'];
        $tbname = $this->getTableName($uid);
    	$sql = "SELECT uid,method,create_time FROM $tbname WHERE uid=:uid ";
        return $rdb->fetchAll($sql, array('uid'=>$uid));
    }

    public function insertUserExchange($uid, $method)
    {
    	$db = Hapyfish2_Db_Factory::getDB($uid);
        $wdb = $db['w'];
        $tbname = $this->getTableName($uid);
        return $wdb->insert($tbname, array('uid'=>$uid, 'method'=>$method, 'create_time'=>time()));
    }
    
    public function checkExchange($uid, $method)
    {
    	$db = Hapyfish2_Db_Factory::getDB($uid);
        $rdb = $db['r'];
        $tbname = $this->getTableName($uid);
    	$sql = "SELECT uid FROM $tbname WHERE uid=:uid and method=:method";
        return $rdb->fetchAll($sql, array('uid'=>$uid, 'method'=>$method));
    }

}